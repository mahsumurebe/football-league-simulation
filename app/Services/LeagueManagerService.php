<?php

namespace App\Services;

use App\Models\Game;
use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class LeagueManagerService
{
    private GameSimulatorService $gameSimulator;

    public function __construct(GameSimulatorService $gameSimulator)
    {
        $this->gameSimulator = $gameSimulator;
    }

    public function initializeStandings(): void
    {
        LeagueStanding::truncate();

        $teams = Team::all();

        foreach ($teams as $team) {
            LeagueStanding::create([
                'team_id' => $team->id,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ]);
        }
    }

    public function simulateWeek(int $week): array
    {
        return DB::transaction(function () use ($week) {
            $games = Game::where('week', $week)->notPlayed()->with(['homeTeam', 'awayTeam'])->get();

            $results = [];

            foreach ($games as $game) {
                $score = $this->gameSimulator->simulateGame($game);

                $game->update([
                    'home_score' => $score['home_score'],
                    'away_score' => $score['away_score'],
                    'played' => true,
                ]);

                $this->updateStandings($game);

                $results[] = [
                    'match_id' => $game->id,
                    'home_team' => $game->homeTeam->name,
                    'away_team' => $game->awayTeam->name,
                    'score' => "{$score['home_score']} - {$score['away_score']}",
                ];
            }

            return $results;
        });
    }

    public function simulateAll(): array
    {
        return DB::transaction(function () {
            $allResults = [];
            $currentWeek = Game::notPlayed()->min('week') ?? 1;
            $maxWeek = Game::max('week');

            for ($week = $currentWeek; $week <= $maxWeek; $week++) {
                $allResults["Week {$week}"] = $this->simulateWeek($week);
            }

            return $allResults;
        });
    }

    protected function updateStandings(Game $game): void
    {
        $homeStanding = LeagueStanding::where('team_id', $game->home_team_id)->first();
        $awayStanding = LeagueStanding::where('team_id', $game->away_team_id)->first();

        if (! $homeStanding) {
            $homeStanding = LeagueStanding::create([
                'team_id' => $game->home_team_id,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ]);
        }

        if (! $awayStanding) {
            $awayStanding = LeagueStanding::create([
                'team_id' => $game->away_team_id,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ]);
        }

        $homeStanding->played++;
        $homeStanding->goals_for += $game->home_score;
        $homeStanding->goals_against += $game->away_score;

        if ($game->home_score > $game->away_score) {
            $homeStanding->won++;
            $homeStanding->points += 3;
        } elseif ($game->home_score == $game->away_score) {
            $homeStanding->drawn++;
            $homeStanding->points += 1;
        } else {
            $homeStanding->lost++;
        }

        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $homeStanding->save();

        $awayStanding->played++;
        $awayStanding->goals_for += $game->away_score;
        $awayStanding->goals_against += $game->home_score;

        if ($game->away_score > $game->home_score) {
            $awayStanding->won++;
            $awayStanding->points += 3;
        } elseif ($game->away_score == $game->home_score) {
            $awayStanding->drawn++;
            $awayStanding->points += 1;
        } else {
            $awayStanding->lost++;
        }

        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;
        $awayStanding->save();
    }

    /**
     * Revert standings changes for a game (remove old scores from standings)
     */
    private function revertStandings(Game $game): void
    {
        if (! $game->played || $game->home_score === null || $game->away_score === null) {
            return;
        }

        $homeStanding = LeagueStanding::where('team_id', $game->home_team_id)->first();
        $awayStanding = LeagueStanding::where('team_id', $game->away_team_id)->first();

        if (! $homeStanding || ! $awayStanding) {
            return;
        }

        $homeStanding->played = max(0, $homeStanding->played - 1);
        $homeStanding->goals_for = max(0, $homeStanding->goals_for - $game->home_score);
        $homeStanding->goals_against = max(0, $homeStanding->goals_against - $game->away_score);

        if ($game->home_score > $game->away_score) {
            $homeStanding->won = max(0, $homeStanding->won - 1);
            $homeStanding->points = max(0, $homeStanding->points - 3);
        } elseif ($game->home_score == $game->away_score) {
            $homeStanding->drawn = max(0, $homeStanding->drawn - 1);
            $homeStanding->points = max(0, $homeStanding->points - 1);
        } else {
            $homeStanding->lost = max(0, $homeStanding->lost - 1);
        }

        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $homeStanding->save();

        $awayStanding->played = max(0, $awayStanding->played - 1);
        $awayStanding->goals_for = max(0, $awayStanding->goals_for - $game->away_score);
        $awayStanding->goals_against = max(0, $awayStanding->goals_against - $game->home_score);

        if ($game->away_score > $game->home_score) {
            $awayStanding->won = max(0, $awayStanding->won - 1);
            $awayStanding->points = max(0, $awayStanding->points - 3);
        } elseif ($game->away_score == $game->home_score) {
            $awayStanding->drawn = max(0, $awayStanding->drawn - 1);
            $awayStanding->points = max(0, $awayStanding->points - 1);
        } else {
            $awayStanding->lost = max(0, $awayStanding->lost - 1);
        }

        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;
        $awayStanding->save();
    }

    /**
     * Recalculate all standings from scratch based on all played games
     * This ensures data consistency and prevents negative values
     */
    private function recalculateStandings(): void
    {
        $this->initializeStandings();

        $playedGames = Game::where('played', true)
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        foreach ($playedGames as $game) {
            $this->updateStandings($game);
        }
    }

    public function getLeagueTable(): array
    {
        return LeagueStanding::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get()
            ->toArray();
    }

    public function resetLeague(): void
    {
        DB::transaction(function () {
            Game::query()->update([
                'home_score' => null,
                'away_score' => null,
                'played' => false,
            ]);

            $this->initializeStandings();
        });
    }

    /**
     * Update a game's scores and recalculate standings
     */
    public function updateGame(int $gameId, array $data): Game
    {
        return DB::transaction(function () use ($gameId, $data) {
            $game = Game::with(['homeTeam', 'awayTeam'])->findOrFail($gameId);

            $newHomeScore = $data['home_score'] ?? null;
            $newAwayScore = $data['away_score'] ?? null;

            $game->home_score = $newHomeScore;
            $game->away_score = $newAwayScore;

            if ($newHomeScore !== null && $newAwayScore !== null) {
                $game->played = true;
            } else {
                $game->played = false;
                $game->home_score = null;
                $game->away_score = null;
            }

            $game->save();

            $this->recalculateStandings();

            return $game->fresh(['homeTeam', 'awayTeam']);
        });
    }
}
