<?php

namespace App\Services;

use App\Enums\Outcome;
use App\Models\Game;
use App\Models\Team;

class GameSimulatorService
{
    private const HOME_ADVANTAGE = 5;
    private const BASE_DRAW_CHANCE = 20;

    public function simulateGame(Game $game)
    {
        $homeTeam = $game->homeTeam;
        $awayTeam = $game->awayTeam;

        $homeStrength = $homeTeam->power + self::HOME_ADVANTAGE;
        $awayStrength = $awayTeam->power;

        $totalStrength = $homeStrength + $awayStrength;
        $homeWinChance = ($homeStrength / $totalStrength) * 100;
        $awayWinChance = ($awayStrength / $totalStrength) * 100;

        $homeWinChance = $homeWinChance - (self::BASE_DRAW_CHANCE / 2);
        $awayWinChance = $awayWinChance - (self::BASE_DRAW_CHANCE / 2);

        $random = mt_rand(1, 100);

        if ($random <= $homeWinChance) {
            return $this->generateScore($homeTeam, $awayTeam, Outcome::HOME_WIN);
        } elseif ($random <= ($homeWinChance + self::BASE_DRAW_CHANCE)) {
            return $this->generateScore($homeTeam, $awayTeam, Outcome::DRAW);
        } else {
            return $this->generateScore($homeTeam, $awayTeam, Outcome::AWAY_WIN);
        }
    }

    private function generateScore(Team $homeTeam, Team $awayTeam, Outcome $outcome): array
    {
        switch ($outcome) {
            case Outcome::HOME_WIN:
                $homeScore = mt_rand(1, 3);
                $awayScore = max(0, $homeScore - mt_rand(1, 2));
                break;

            case Outcome::DRAW:
                $score = mt_rand(0, 2);
                $homeScore = $score;
                $awayScore = $score;
                break;

            case Outcome::AWAY_WIN:
                $awayScore = mt_rand(1, 3);
                $homeScore = max(0, $awayScore - mt_rand(1, 2));
                break;

            default:
                $homeScore = 0;
                $awayScore = 0;
        }

        return [
            'home_score' => $homeScore,
            'away_score' => $awayScore,
        ];
    }
}
