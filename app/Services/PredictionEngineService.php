<?php

namespace App\Services;

use App\Models\ChampionshipPrediction;
use App\Models\Game;
use App\Models\LeagueStanding;
use App\Models\Team;

class PredictionEngineService
{
    public function calculatePredictions(int $currentWeek): array
    {
        if ($currentWeek < 4) {
            return ['error' => 'Predictions available after week 4'];
        }

        $standings = LeagueStanding::with('team')->orderBy('points', 'desc')->get();
        $teams = Team::all();
        $maxWeek = Game::max('week');
        $remainingWeeks = $maxWeek - $currentWeek;

        $totalGames = Game::count();
        $playedGames = Game::where('played', true)->count();
        $allGamesPlayed = ($totalGames > 0 && $playedGames === $totalGames);

        $predictions = [];

        foreach ($teams as $team) {
            $standing = $standings->where('team_id', $team->id)->first();
            $currentPoints = $standing->points;

            if ($allGamesPlayed) {
                $topTeam = $standings->first();
                $probability = ($team->id === $topTeam->team_id) ? 100.0 : 0.0;
                
                $predictions[] = [
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'current_points' => $currentPoints,
                    'projected_points' => $currentPoints,
                    'probability' => $probability,
                ];

                ChampionshipPrediction::updateOrCreate(
                    ['team_id' => $team->id, 'week' => $currentWeek],
                    ['probability' => $probability]
                );
                
                continue;
            }

            $remainingGames = Game::where(function ($query) use ($team) {
                $query->where('home_team_id', $team->id)
                    ->orWhere('away_team_id', $team->id);
            })
                ->where('played', false)
                ->with(['homeTeam', 'awayTeam'])
                ->get();

            $expectedPoints = 0;
            foreach ($remainingGames as $game) {
                $isHome = $game->home_team_id == $team->id;
                $opponent = $isHome ? $game->awayTeam : $game->homeTeam;

                $winProbability = $this->calculateWinProbability($team, $opponent, $isHome);
                $expectedPoints += ($winProbability * 3) + ((1 - $winProbability) * 0.5);
            }

            $projectedPoints = $currentPoints + $expectedPoints;

            $probability = $this->calculateChampionshipProbability(
                $projectedPoints,
                $standings,
                $team->id
            );

            $predictions[] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'current_points' => $currentPoints,
                'projected_points' => round($projectedPoints, 1),
                'probability' => round($probability, 2),
            ];
        }

        if (!$allGamesPlayed) {
            $totalProbability = array_sum(array_column($predictions, 'probability'));
            
            if ($totalProbability > 0) {
                for ($i = 0; $i < count($predictions); $i++) {
                    $predictions[$i]['probability'] = round(($predictions[$i]['probability'] / $totalProbability) * 100, 2);
                }
            } else {
                $equalProbability = 100.0 / count($predictions);
                for ($i = 0; $i < count($predictions); $i++) {
                    $predictions[$i]['probability'] = round($equalProbability, 2);
                }
            }
        }

        foreach ($predictions as $prediction) {
            ChampionshipPrediction::updateOrCreate(
                ['team_id' => $prediction['team_id'], 'week' => $currentWeek],
                ['probability' => $prediction['probability']]
            );
        }

        usort($predictions, function ($a, $b) {
            return $b['probability'] <=> $a['probability'];
        });

        return $predictions;
    }

    private function calculateWinProbability(Team $team, Team $opponent, bool $isHome): float
    {
        $teamPower = $team->power + ($isHome ? 5 : 0);
        $opponentPower = $opponent->power + (! $isHome ? 5 : 0);

        $totalPower = $teamPower + $opponentPower;

        return $teamPower / $totalPower;
    }

    private function calculateChampionshipProbability(
        float $projectedPoints,
        $standings,
        int $teamId
    ): float {
        $topPoints = $standings->first()->points;
        $bottomPoints = $standings->last()->points;
        $pointsRange = max($topPoints - $bottomPoints, 1);

        $relativeProbability = ($projectedPoints - $bottomPoints) / $pointsRange;

        return pow($relativeProbability, 2) * 100;
    }
}
