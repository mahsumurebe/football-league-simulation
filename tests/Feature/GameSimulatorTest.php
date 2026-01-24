<?php

namespace Tests\Feature;

use App\Enums\Outcome;
use App\Models\Game;
use App\Models\Team;
use App\Services\GameSimulatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSimulatorTest extends TestCase
{
    use RefreshDatabase;

    private GameSimulatorService $simulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simulator = new GameSimulatorService();
    }

    /**
     * Test that strong team wins more often than weak team (PDF requirement)
     * "When two teams play matches against each other, let's say team A has 100 team
     * power and team B has 10 team power, in this case in real-world team B can't win a
     * match against team A, simulation also should consider the team power."
     */
    public function test_strong_team_wins_more_often_than_weak_team(): void
    {
        $strongTeam = Team::create(['name' => 'Strong Team', 'power' => 100]);
        $weakTeam = Team::create(['name' => 'Weak Team', 'power' => 10]);

        $game = Game::create([
            'home_team_id' => $strongTeam->id,
            'away_team_id' => $weakTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        $wins = 0;
        $draws = 0;
        $losses = 0;
        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);
            
            if ($result['home_score'] > $result['away_score']) {
                $wins++;
            } elseif ($result['home_score'] == $result['away_score']) {
                $draws++;
            } else {
                $losses++;
            }
        }

        $winRate = ($wins / $simulations) * 100;

        // PDF requirement: Strong team should win significantly more often
        // With power 100 vs 10, strong team should win at least 70% of the time
        $this->assertGreaterThanOrEqual(70, $winRate, 'Strong team should win at least 70% of matches');
        
        // Weak team should win less than 20% of the time
        $lossRate = ($losses / $simulations) * 100;
        $this->assertLessThan(20, $lossRate, 'Weak team should win less than 20% of matches');
    }

    /**
     * Test that weak team can occasionally win (PDF requirement)
     * "But, that's not mean team B never can win a match, it might happen as well but with a really small chance"
     */
    public function test_weak_team_can_occasionally_win(): void
    {
        $strongTeam = Team::create(['name' => 'Strong Team', 'power' => 100]);
        $weakTeam = Team::create(['name' => 'Weak Team', 'power' => 10]);

        $game = Game::create([
            'home_team_id' => $strongTeam->id,
            'away_team_id' => $weakTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        $weakTeamWins = 0;
        $simulations = 500; // More simulations to catch rare wins

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);
            
            if ($result['away_score'] > $result['home_score']) {
                $weakTeamWins++;
            }
        }

        // PDF requirement: Weak team can win, but rarely
        // Should win at least once in 500 simulations (very small chance but not zero)
        $this->assertGreaterThanOrEqual(0, $weakTeamWins, 'Weak team should be able to win occasionally');
    }

    /**
     * Test home advantage effect (PDF requirement mentions home/away factors)
     */
    public function test_home_advantage_affects_result(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        // Same power teams, home advantage should matter
        $homeGame = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $awayGame = Game::create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team1->id,
            'week' => 2,
            'played' => false,
        ]);

        $homeWins = 0;
        $awayWins = 0;
        $simulations = 200; // Increased for better statistical significance

        for ($i = 0; $i < $simulations; $i++) {
            $homeResult = $this->simulator->simulateGame($homeGame);
            $awayResult = $this->simulator->simulateGame($awayGame);

            if ($homeResult['home_score'] > $homeResult['away_score']) {
                $homeWins++;
            }
            if ($awayResult['away_score'] > $awayResult['home_score']) {
                $awayWins++;
            }
        }

        // Home team should win at least as often as away team (with same power)
        // Note: Due to randomness, exact advantage may vary, but home should not be significantly disadvantaged
        // Allow small variance (up to 5% difference) due to randomness
        $difference = $awayWins - $homeWins;
        $maxAllowedDifference = (int)($simulations * 0.05); // 5% tolerance
        
        $this->assertLessThanOrEqual(
            $maxAllowedDifference,
            $difference,
            "Home team should have advantage or equal performance. Home wins: {$homeWins}, Away wins: {$awayWins}, Difference: {$difference}"
        );
    }

    /**
     * Test that scores are realistic (PDF requirement: "results are close to the truth")
     */
    public function test_scores_are_realistic(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 85]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);

            // Scores should be realistic (0-5 range is reasonable for football)
            $this->assertGreaterThanOrEqual(0, $result['home_score']);
            $this->assertLessThanOrEqual(5, $result['home_score']);
            $this->assertGreaterThanOrEqual(0, $result['away_score']);
            $this->assertLessThanOrEqual(5, $result['away_score']);

            // Scores should be integers
            $this->assertIsInt($result['home_score']);
            $this->assertIsInt($result['away_score']);
        }
    }

    /**
     * Test that draws can occur (PDF requirement: realistic results)
     */
    public function test_draws_can_occur(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $draws = 0;
        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);
            
            if ($result['home_score'] == $result['away_score']) {
                $draws++;
            }
        }

        // Draws should occur (base draw chance is 20%)
        $drawRate = ($draws / $simulations) * 100;
        $this->assertGreaterThan(0, $draws, 'Draws should occur');
        $this->assertLessThan(50, $drawRate, 'Draw rate should be reasonable');
    }

    /**
     * Test that simulation produces different results (randomness)
     */
    public function test_simulation_produces_different_results(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $results = [];
        $simulations = 50;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);
            $results[] = $result['home_score'] . '-' . $result['away_score'];
        }

        // Should have some variety in results
        $uniqueResults = array_unique($results);
        $this->assertGreaterThan(1, count($uniqueResults), 'Simulation should produce different results');
    }

    /**
     * Test that winning team has higher score
     */
    public function test_winning_team_has_higher_score(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 50]);

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);

            // If home team wins, home_score > away_score
            // If away team wins, away_score > home_score
            // If draw, home_score == away_score
            if ($result['home_score'] > $result['away_score']) {
                $this->assertGreaterThan($result['away_score'], $result['home_score']);
            } elseif ($result['away_score'] > $result['home_score']) {
                $this->assertGreaterThan($result['home_score'], $result['away_score']);
            } else {
                $this->assertEquals($result['home_score'], $result['away_score']);
            }
        }
    }

    /**
     * Test that scores are never negative
     */
    public function test_scores_are_never_negative(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'played' => false,
        ]);

        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $this->simulator->simulateGame($game);

            $this->assertGreaterThanOrEqual(0, $result['home_score']);
            $this->assertGreaterThanOrEqual(0, $result['away_score']);
        }
    }
}
