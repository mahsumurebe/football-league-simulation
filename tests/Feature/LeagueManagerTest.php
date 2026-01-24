<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\GameSimulatorService;
use App\Services\LeagueManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueManagerTest extends TestCase
{
    use RefreshDatabase;

    private LeagueManagerService $leagueManager;
    private FixtureGeneratorService $fixtureGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $gameSimulator = new GameSimulatorService();
        $this->leagueManager = new LeagueManagerService($gameSimulator);
        $this->fixtureGenerator = new FixtureGeneratorService();
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
     * Test Premier League scoring: Win = 3 points (PDF requirement)
     */
    public function test_win_awards_three_points(): void
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

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // PDF requirement: Win = 3 points
        $this->assertEquals(3, $standing1->points);
        $this->assertEquals(0, $standing2->points);
        $this->assertEquals(1, $standing1->won);
        $this->assertEquals(1, $standing2->lost);
    }

    /**
     * Test Premier League scoring: Draw = 1 point (PDF requirement)
     */
    public function test_draw_awards_one_point_each(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 85]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 85]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 1,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);

        $this->updateStandings($game);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // PDF requirement: Draw = 1 point each
        $this->assertEquals(1, $standing1->points);
        $this->assertEquals(1, $standing2->points);
        $this->assertEquals(1, $standing1->drawn);
        $this->assertEquals(1, $standing2->drawn);
    }

    /**
     * Test Premier League scoring: Loss = 0 points (PDF requirement)
     */
    public function test_loss_awards_zero_points(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 90]);

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
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        // PDF requirement: Loss = 0 points
        $this->assertEquals(0, $standing1->points);
        $this->assertEquals(3, $standing2->points);
        $this->assertEquals(1, $standing1->lost);
        $this->assertEquals(1, $standing2->won);
    }

    /**
     * Test that standings include all required fields (PDF requirement)
     */
    public function test_standings_include_all_required_fields(): void
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

        // PDF requirement: Points, Played, Won, Drawn, Lost, Goals For, Goals Against, Goal Difference
        $this->assertNotNull($standing1->points);
        $this->assertNotNull($standing1->played);
        $this->assertNotNull($standing1->won);
        $this->assertNotNull($standing1->drawn);
        $this->assertNotNull($standing1->lost);
        $this->assertNotNull($standing1->goals_for);
        $this->assertNotNull($standing1->goals_against);
        $this->assertNotNull($standing1->goal_difference);

        $this->assertEquals(1, $standing1->played);
        $this->assertEquals(3, $standing1->goals_for);
        $this->assertEquals(1, $standing1->goals_against);
        $this->assertEquals(2, $standing1->goal_difference); // 3 - 1 = 2
    }

    /**
     * Test goal difference calculation (PDF requirement)
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
        $this->assertEquals(-3, $standing2->goal_difference); // 2 - 5 = -3 (negative is normal)
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

        // Update standings for all games using updateGame (which recalculates standings)
        foreach (Game::where('played', true)->get() as $game) {
            $this->leagueManager->updateGame($game->id, [
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
            ]);
        }

        $table = $this->leagueManager->getLeagueTable();

        // PDF requirement: Order by Points > Goal Difference > Goals For
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

        // Update standings for all games using updateGame (which recalculates standings)
        foreach (Game::where('played', true)->get() as $game) {
            $this->leagueManager->updateGame($game->id, [
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
            ]);
        }

        $table = $this->leagueManager->getLeagueTable();

        // PDF requirement: Order by Points > Goal Difference > Goals For
        // Both teams have 6 points and GD +3, but Team 1 has more goals for (5 vs 4)
        // Team 1 should be ranked higher
        $team1Standing = collect($table)->firstWhere('team_id', $teams[0]->id);
        $team2Standing = collect($table)->firstWhere('team_id', $teams[1]->id);

        $this->assertEquals(6, $team1Standing['points']);
        $this->assertEquals(6, $team2Standing['points']);
        $this->assertEquals(3, $team1Standing['goal_difference']);
        $this->assertEquals(3, $team2Standing['goal_difference']);
        $this->assertEquals(5, $team1Standing['goals_for']);
        $this->assertEquals(4, $team2Standing['goals_for']);

        // Team 1 should be ranked higher than Team 2 (same points and GD, but more goals for)
        $team1Position = collect($table)->search(function ($standing) use ($teams) {
            return $standing['team_id'] === $teams[0]->id;
        });
        $team2Position = collect($table)->search(function ($standing) use ($teams) {
            return $standing['team_id'] === $teams[1]->id;
        });

        $this->assertLessThan($team2Position, $team1Position, 'Team 1 should be ranked higher than Team 2 when points and GD are equal but Team 1 has more goals for');
    }

    /**
     * Test week simulation (PDF requirement: week-by-week simulation)
     */
    public function test_simulate_week_plays_only_that_week(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // Simulate week 1
        $results = $this->leagueManager->simulateWeek(1);

        // Week 1 games should be played
        $week1Games = Game::where('week', 1)->get();
        foreach ($week1Games as $game) {
            $this->assertTrue($game->played);
            $this->assertNotNull($game->home_score);
            $this->assertNotNull($game->away_score);
        }

        // Week 2 games should NOT be played
        $week2Games = Game::where('week', 2)->get();
        foreach ($week2Games as $game) {
            $this->assertFalse($game->played);
            $this->assertNull($game->home_score);
            $this->assertNull($game->away_score);
        }

        // Should return results for week 1
        $this->assertCount(2, $results); // 2 matches per week
    }

    /**
     * Test that standings update after week simulation
     */
    public function test_standings_update_after_week_simulation(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // All standings should start at 0
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            $this->assertEquals(0, $standing->played);
            $this->assertEquals(0, $standing->points);
        }

        // Simulate week 1
        $this->leagueManager->simulateWeek(1);

        // All teams should have played 1 match
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            $this->assertEquals(1, $standing->played);
            $this->assertGreaterThanOrEqual(0, $standing->points);
            $this->assertLessThanOrEqual(3, $standing->points);
        }
    }

    /**
     * Test simulate all games (PDF requirement: "Play All" button)
     */
    public function test_simulate_all_plays_all_remaining_games(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $results = $this->leagueManager->simulateAll();

        // All games should be played
        $allGames = Game::all();
        foreach ($allGames as $game) {
            $this->assertTrue($game->played);
            $this->assertNotNull($game->home_score);
            $this->assertNotNull($game->away_score);
        }

        // Should have results for all 6 weeks
        $this->assertCount(6, $results);
        $this->assertArrayHasKey('Week 1', $results);
        $this->assertArrayHasKey('Week 6', $results);

        // Each team should have played 6 matches
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            $this->assertEquals(6, $standing->played);
        }
    }

    /**
     * Test that all games completed message appears when no games left
     */
    public function test_all_games_completed_when_no_games_left(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // Simulate all games
        $this->leagueManager->simulateAll();

        // Try to simulate again - should find no games
        $nextWeek = Game::notPlayed()->min('week');
        $this->assertNull($nextWeek);
    }

    /**
     * Test reset league functionality
     */
    public function test_reset_league_resets_all_games_and_standings(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // Simulate some games
        $this->leagueManager->simulateWeek(1);
        $this->leagueManager->simulateWeek(2);

        // Reset league
        $this->leagueManager->resetLeague();

        // All games should be reset
        $allGames = Game::all();
        foreach ($allGames as $game) {
            $this->assertFalse($game->played);
            $this->assertNull($game->home_score);
            $this->assertNull($game->away_score);
        }

        // All standings should be reset
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            $this->assertEquals(0, $standing->played);
            $this->assertEquals(0, $standing->points);
            $this->assertEquals(0, $standing->won);
            $this->assertEquals(0, $standing->drawn);
            $this->assertEquals(0, $standing->lost);
            $this->assertEquals(0, $standing->goals_for);
            $this->assertEquals(0, $standing->goals_against);
            $this->assertEquals(0, $standing->goal_difference);
        }
    }

    /**
     * Test that negative goal difference is allowed (normal in football)
     */
    public function test_negative_goal_difference_is_allowed(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 80]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 90]);

        $this->leagueManager->initializeStandings();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 1,
            'away_score' => 5,
            'week' => 1,
            'played' => true,
        ]);

        $this->leagueManager->updateGame($game->id, [
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ]);

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(-4, $standing1->goal_difference); // 1 - 5 = -4 (normal)
    }

    /**
     * Test that standings are created if they don't exist
     */
    public function test_standings_created_if_not_exist(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);

        // Don't initialize standings
        // LeagueStanding::truncate();

        $game = Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);

        $this->leagueManager->updateGame($game->id, [
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ]);

        // Standings should be created automatically
        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $standing2 = LeagueStanding::where('team_id', $team2->id)->first();

        $this->assertNotNull($standing1);
        $this->assertNotNull($standing2);
        $this->assertEquals(3, $standing1->points);
    }

    /**
     * Test that played count increments correctly
     */
    public function test_played_count_increments_correctly(): void
    {
        $team1 = Team::create(['name' => 'Team 1', 'power' => 90]);
        $team2 = Team::create(['name' => 'Team 2', 'power' => 80]);
        $team3 = Team::create(['name' => 'Team 3', 'power' => 85]);

        $this->leagueManager->initializeStandings();

        // Team 1 plays 2 games
        Game::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'week' => 1,
            'played' => true,
        ]);

        Game::create([
            'home_team_id' => $team3->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'week' => 2,
            'played' => true,
        ]);

        foreach (Game::where('played', true)->get() as $game) {
            $this->leagueManager->updateGame($game->id, [
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
            ]);
        }

        $standing1 = LeagueStanding::where('team_id', $team1->id)->first();
        $this->assertEquals(2, $standing1->played);
    }
}
