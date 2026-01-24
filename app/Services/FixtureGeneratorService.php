<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Team;

class FixtureGeneratorService
{
    public function generateFixtures(): void
    {
        Game::truncate();

        $teams = Team::all();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            throw new \Exception("At least two teams required to generate fixtures");
        }

        $week = 1;
        $fixtures = [];

        for ($round = 0; $round < ($teamCount - 1); $round++) {
            for ($match = 0; $match < $teamCount / 2; $match++) {
                $home = ($round + $match) % ($teamCount - 1);
                $away = ($teamCount - 1 - $match + $round) % ($teamCount - 1);

                if ($match == 0) {
                    $away = $teamCount - 1;
                }

                $fixtures[] = [
                    'home_team_id' => $teams[$home]->id,
                    'away_team_id' => $teams[$away]->id,
                    'week' => $week,
                    'played' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $week++;
        }

        $reverseFixtures = [];
        foreach ($fixtures as $index => $fixture) {
            if ($index > 0 && $index % ($teamCount / 2) == 0) {
                $week++;
            }
            
            $reverseFixtures[] = [
                'home_team_id' => $fixture['away_team_id'],
                'away_team_id' => $fixture['home_team_id'],
                'week' => $week,
                'played' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $fixtures = array_merge($fixtures, $reverseFixtures);

        Game::insert($fixtures);
    }
}