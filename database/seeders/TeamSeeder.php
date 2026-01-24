<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\LeagueManagerService;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        Team::truncate();
        $teams = [
            ['name' => 'Chelsea', 'power' => 90],
            ['name' => 'Arsenal', 'power' => 85],
            ['name' => 'Manchester City', 'power' => 88],
            ['name' => 'Liverpool', 'power' => 82],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }

        // Generate fixtures
        $fixtureGenerator = new FixtureGeneratorService();
        $fixtureGenerator->generateFixtures();

        // Initialize league standings
        $leagueManager = app(LeagueManagerService::class);
        $leagueManager->initializeStandings();

        $this->command->info('Teams, fixtures, and standings created successfully!');
    }
}
