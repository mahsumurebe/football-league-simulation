<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\LeagueManagerService;
use App\Services\GameSimulatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingsCalculationTest extends TestCase
{
    use RefreshDatabase;

    private LeagueManagerService $leagueManager;

    protected function setUp(): void
    {
        parent::setUp();
        $gameSimulator = new GameSimulatorService();
        $this->leagueManager = new LeagueManagerService($gameSimulator);
    }

    /**
     * Helper method to call protected updateStandings method
     */
    private function updateStandings(Game $game): void
    {
        $reflection = new \ReflectionClass($this->leagueManager);
        $method = $reflection->getMethod('updateStandings');
        $method->setAccessible(true);
        $method->invoke($this->leagueManager, $game);
    }

    /**
     * Test that points calculation follows Premier League rules (PDF requirement)
     */
    public function test_points_calculation_follows_premier_league_rules(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        // Win = 3 points
        $game1 = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);
        $this->updateStandings($game1);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(3, $standing1->points, 'Win should award 3 points');

        // Draw = 1 point each
        $game2 = Game::create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);
        $this->updateStandings($game2);

        $standing1->refresh();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();
        $this->assertEquals(4, $standing1->points, 'Team 1 should have 3 + 1 = 4 points');
        $this->assertEquals(1, $standing2->points, 'Team 2 should have 0 + 1 = 1 point');

        // Loss = 0 points
        $game3 = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 0,
            'away_score' => 2,
            'week' => 3,
            'played' => true,
        ]);
        $this->updateStandings($game3);

        $standing1->refresh();
        $standing2->refresh();
        $this->assertEquals(4, $standing1->points, 'Team 1 should still have 4 points');
        $this->assertEquals(4, $standing2->points, 'Team 2 should have 1 + 3 = 4 points');
    }

    /**
     * Test that won, drawn, lost counts are correct
     */
    public function test_won_drawn_lost_counts_are_correct(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        // Win
        $game1 = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);
        $this->updateStandings($game1);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();
        $this->assertEquals(1, $standing1->won);
        $this->assertEquals(0, $standing1->drawn);
        $this->assertEquals(0, $standing1->lost);
        $this->assertEquals(0, $standing2->won);
        $this->assertEquals(0, $standing2->drawn);
        $this->assertEquals(1, $standing2->lost);

        // Draw
        $game2 = Game::create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);
        $this->updateStandings($game2);

        $standing1->refresh();
        $standing2->refresh();
        $this->assertEquals(1, $standing1->won);
        $this->assertEquals(1, $standing1->drawn);
        $this->assertEquals(0, $standing1->lost);
        $this->assertEquals(0, $standing2->won);
        $this->assertEquals(1, $standing2->drawn);
        $this->assertEquals(1, $standing2->lost);
    }

    /**
     * Test that goals_for and goals_against are calculated correctly
     */
    public function test_goals_for_and_against_calculation(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 3,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // Home team: goals_for = 3, goals_against = 1
        $this->assertEquals(3, $standing1->goals_for);
        $this->assertEquals(1, $standing1->goals_against);

        // Away team: goals_for = 1, goals_against = 3
        $this->assertEquals(1, $standing2->goals_for);
        $this->assertEquals(3, $standing2->goals_against);
    }

    /**
     * Test that goal difference is calculated correctly
     */
    public function test_goal_difference_calculation(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 5,
            'away_score' => 2,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // Goal difference = goals_for - goals_against
        $this->assertEquals(3, $standing1->goal_difference); // 5 - 2 = 3
        $this->assertEquals(-3, $standing2->goal_difference); // 2 - 5 = -3
    }

    /**
     * Test that played count increments correctly
     */
    public function test_played_count_increments(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);
        $team3 = Team::create(['name' => 'Team 3', 'power' => 85]);

        $this->leagueManager->initializeStandings();

        // Team 1 plays 2 games
        $game1 = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);
        $this->updateStandings($game1);

        $game2 = Game::create([
            'home_team_id' => $team3->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);
        $this->updateStandings($game2);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(2, $standing1->played);
    }

    /**
     * Test standings ordering: Points > Goal Difference > Goals For (PDF requirement)
     */
    public function test_standings_ordering_by_premier_league_rules(): void
    {
        $teams = [
            Team::create(['name' => 'Team 1', 'power' => 90]),
            Team::create(['name' => 'Team 2', 'power' => 85]),
            Team::create(['name' => 'Team 3', 'power' => 80]),
            Team::create(['name' => 'Team 4', 'power' => 75]),
        ];

        $this->leagueManager->initializeStandings();

        // Team 1: 6 points, GD +3, GF 5
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'home_score' => 3,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);
        Game::create([
            'home_team_id' => $teams[2]->id,
            'away_team_id' => $teams[0]->id,
            'home_score' => 1,
            'away_score' => 2,
            'week' => 2,
            'played' => true,
        ]);

        // Team 2: 6 points, GD +2, GF 4
        Game::create([
            'home_team_id' => $teams[1]->id,
            'away_team_id' => $teams[2]->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);
        Game::create([
            'home_team_id' => $teams[3]->id,
            'away_team_id' => $teams[1]->id,
            'home_score' => 0,
            'away_score' => 2,
            'week' => 3,
            'played' => true,
        ]);

        // Update standings
        foreach (Game::where('played', true)->get() as $game) {
            $this->updateStandings($game);
        }

        $table = $this->leagueManager->getLeagueTable();

        // Team 1 should be first (same points but better GD)
        $this->assertEquals($teams[0]->id, $table[0]['team_id']);
        $this->assertEquals($teams[1]->id, $table[1]['team_id']);
    }

    /**
     * Test standings ordering by Goals For when Points and Goal Difference are equal (PDF requirement)
     * Order: Points > Goal Difference > Goals For
     */
    public function test_standings_ordering_by_goals_for_when_points_and_gd_equal(): void
    {
        $teams = [
            Team::create(['name' => 'Team 1', 'power' => 90]),
            Team::create(['name' => 'Team 2', 'power' => 85]),
            Team::create(['name' => 'Team 3', 'power' => 80]),
            Team::create(['name' => 'Team 4', 'power' => 75]),
        ];

        $this->leagueManager->initializeStandings();

        // Team 1: 6 points, GD +3, GF 5
        // Team 1 vs Team 3: 3-1 (3 goals for, 1 against)
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[2]->id,
            'home_score' => 3,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);
        // Team 1 vs Team 4: 2-1 (2 goals for, 1 against)
        // Total: 5 goals for, 2 goals against = GD +3, Points 6
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[3]->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);

        // Team 2: 6 points, GD +3, GF 4 (same points and GD, but fewer goals for)
        // Team 2 vs Team 3: 2-0 (2 goals for, 0 against)
        Game::create([
            'home_team_id' => $teams[1]->id,
            'away_team_id' => $teams[2]->id,
            'home_score' => 2,
            'away_score' => 0,
            'week' => 1,
            'played' => true,
        ]);
        // Team 2 vs Team 4: 2-1 (2 goals for, 1 against)
        // Total: 4 goals for, 1 goal against = GD +3, Points 6
        Game::create([
            'home_team_id' => $teams[1]->id,
            'away_team_id' => $teams[3]->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);

        // Update standings
        foreach (Game::where('played', true)->get() as $game) {
            $this->updateStandings($game);
        }

        $table = $this->leagueManager->getLeagueTable();

        // PDF requirement: Order by Points > Goal Difference > Goals For
        // Both teams have 6 points and GD +3, but Team 1 has more goals for (5 vs 4)
        // Team 1 should be ranked higher
        $this->assertEquals($teams[0]->id, $table[0]['team_id'], 'Team 1 should be first (same points and GD, but more Goals For)');
        $this->assertEquals($teams[1]->id, $table[1]['team_id'], 'Team 2 should be second');
        $this->assertEquals(6, $table[0]['points']);
        $this->assertEquals(6, $table[1]['points']);
        $this->assertEquals(3, $table[0]['goal_difference']);
        $this->assertEquals(3, $table[1]['goal_difference']);
        $this->assertEquals(5, $table[0]['goals_for']);
        $this->assertEquals(4, $table[1]['goals_for']);
    }

    /**
     * Test edge case: Team with zero goals scored
     */
    public function test_team_with_zero_goals_scored(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 0,
            'away_score' => 2,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(0, $standing1->goals_for);
        $this->assertEquals(2, $standing1->goals_against);
        $this->assertEquals(-2, $standing1->goal_difference);
        $this->assertEquals(0, $standing1->points);
    }

    /**
     * Test edge case: All games are draws
     */
    public function test_all_games_are_draws(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 85]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 85]);

        $this->leagueManager->initializeStandings();

        // Play 3 draws
        for ($i = 1; $i <= 3; $i++) {
            $game = Game::create([
                'home_team_id' => $team1->id,
                'away_team_id' => $team2->id,
                'home_score' => 1,
                'away_score' => 1,
                'week' => $i,
                'played' => true,
            ]);
            $this->updateStandings($game);
        }

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // Both should have 3 points (3 draws * 1 point each)
        $this->assertEquals(3, $standing1->points);
        $this->assertEquals(3, $standing2->points);
        $this->assertEquals(3, $standing1->drawn);
        $this->assertEquals(3, $standing2->drawn);
        $this->assertEquals(0, $standing1->won);
        $this->assertEquals(0, $standing2->won);
        $this->assertEquals(0, $standing1->lost);
        $this->assertEquals(0, $standing2->lost);
    }

    /**
     * Test edge case: Team never concedes a goal
     */
    public function test_team_never_concedes_goal(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 3,
            'away_score' => 0,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(0, $standing1->goals_against);
        $this->assertEquals(3, $standing1->goal_difference);
    }

    /**
     * Test that standings are recalculated correctly after game update
     */
    public function test_standings_recalculated_after_game_update(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1Before = LeagueStanding::where('team_id', $team1->id)->first();
        $pointsBefore = $standing1Before->points;

        // Update game to draw
        $this->leagueManager->updateGame($game->id, [
            'home_score' => 1,
            'away_score' => 1,
        ]);

        $standing1After = LeagueStanding::where('team_id', $team1->id)->first();
        
        // Points should change from 3 (win) to 1 (draw)
        $this->assertNotEquals($pointsBefore, $standing1After->points);
        $this->assertEquals(1, $standing1After->points);
    }

    /**
     * Test that standings maintain consistency with multiple games
     */
    public function test_standings_consistency_with_multiple_games(): void
    {
        $teams = [
            Team::create(['name' => 'Team 1', 'power' => 90]),
            Team::create(['name' => 'Team 2', 'power' => 85]),
            Team::create(['name' => 'Team 3', 'power' => 80]),
        ];

        $this->leagueManager->initializeStandings();

        // Create multiple games
        $games = [
            Game::create([
                'home_team_id' => $teams[0]->id,
                'away_team_id' => $teams[1]->id,
                'home_score' => 2,
                'away_score' => 1,
                'week' => 1,
                'played' => true,
            ]),
            Game::create([
                'home_team_id' => $teams[1]->id,
                'away_team_id' => $teams[2]->id,
                'home_score' => 1,
                'away_score' => 0,
                'week' => 2,
                'played' => true,
            ]),
            Game::create([
                'home_team_id' => $teams[2]->id,
                'away_team_id' => $teams[0]->id,
                'home_score' => 1,
                'away_score' => 1,
                'week' => 3,
                'played' => true,
            ]),
        ];

        // Update standings for all games
        foreach ($games as $game) {
            $this->updateStandings($game);
        }

        // Recalculate to ensure consistency by updating one game (triggers recalculate)
        if (count($games) > 0) {
            $this->leagueManager->updateGame($games[0]->id, [
                'home_score' => $games[0]->home_score,
                'away_score' => $games[0]->away_score,
            ]);
        }

        // Check consistency
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            
            // Verify: played = won + drawn + lost
            $this->assertEquals(
                $standing->played,
                $standing->won + $standing->drawn + $standing->lost
            );

            // Verify: goal_difference = goals_for - goals_against
            $this->assertEquals(
                $standing->goal_difference,
                $standing->goals_for - $standing->goals_against
            );

            // Verify: points calculation
            $expectedPoints = ($standing->won * 3) + ($standing->drawn * 1);
            $this->assertEquals($expectedPoints, $standing->points);
        }
    }
}
