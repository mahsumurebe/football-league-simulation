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

class LeagueControllerTest extends TestCase
{
    use RefreshDatabase;

    private FixtureGeneratorService $fixtureGenerator;
    private LeagueManagerService $leagueManager;

    protected function setUp(): void
    {
        parent::setUp();
        $gameSimulator = new GameSimulatorService();
        $this->leagueManager = new LeagueManagerService($gameSimulator);
        $this->fixtureGenerator = new FixtureGeneratorService();
    }

    /**
     * Test GET /api/league/table endpoint
     */
    public function test_get_league_table_returns_table(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $response = $this->getJson('/api/league/table');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'team_id',
                    'played',
                    'won',
                    'drawn',
                    'lost',
                    'goals_for',
                    'goals_against',
                    'goal_difference',
                    'points',
                    'team' => [
                        'id',
                        'name',
                        'power',
                    ],
                ],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertCount(4, $data); // 4 teams
    }

    /**
     * Test POST /api/league/simulate-week endpoint
     */
    public function test_simulate_week_simulates_next_week(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $response = $this->postJson('/api/league/simulate-week');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'week',
                'results',
                'table',
                'predictions',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertEquals(1, $data['week']);
        $this->assertCount(2, $data['results']); // 2 matches per week
    }

    /**
     * Test POST /api/league/simulate-week returns all games completed message
     */
    public function test_simulate_week_returns_completed_message_when_all_done(): void
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

        $response = $this->postJson('/api/league/simulate-week');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('All games completed', $response->json('message'));
    }

    /**
     * Test POST /api/league/simulate-all endpoint
     */
    public function test_simulate_all_simulates_all_remaining_games(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $response = $this->postJson('/api/league/simulate-all');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'results',
                'table',
                'predictions',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertCount(6, $data['results']); // 6 weeks

        // All games should be played
        $allGames = Game::all();
        foreach ($allGames as $game) {
            $this->assertTrue($game->played);
        }
    }

    /**
     * Test GET /api/league/matches/week/{week} endpoint
     */
    public function test_get_matches_by_week_returns_matches(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $response = $this->getJson('/api/league/matches/week/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'home_team',
                    'away_team',
                    'score',
                    'played',
                ],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertCount(2, $data); // 2 matches per week
    }

    /**
     * Test GET /api/league/matches/week/{week} with invalid week
     */
    public function test_get_matches_by_week_rejects_invalid_week(): void
    {
        $response = $this->getJson('/api/league/matches/week/0');

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'success',
            'message',
            'error',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('INVALID_WEEK', $response->json('error'));

        $response = $this->getJson('/api/league/matches/week/-1');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    /**
     * Test GET /api/league/matches/week/{week} with non-existent week
     */
    public function test_get_matches_by_week_returns_404_for_nonexistent_week(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $response = $this->getJson('/api/league/matches/week/99');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'error',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('NOT_FOUND', $response->json('error'));
    }

    /**
     * Test PUT /api/league/matches/{id} endpoint
     */
    public function test_update_game_updates_scores(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $game = Game::first();

        $response = $this->putJson("/api/league/matches/{$game->id}", [
            'home_score' => 3,
            'away_score' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'game' => [
                    'id',
                    'home_team',
                    'away_team',
                    'home_score',
                    'away_score',
                    'week',
                    'played',
                ],
                'table',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $game->refresh();
        $this->assertEquals(3, $game->home_score);
        $this->assertEquals(1, $game->away_score);
        $this->assertTrue($game->played);
    }

    /**
     * Test PUT /api/league/matches/{id} rejects negative scores
     */
    public function test_update_game_rejects_negative_scores(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $game = Game::first();

        $response = $this->putJson("/api/league/matches/{$game->id}", [
            'home_score' => -1,
            'away_score' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);
        $this->assertFalse($response->json('success'));
        $response->assertJsonValidationErrors(['home_score']);
    }

    /**
     * Test PUT /api/league/matches/{id} allows null scores (to cancel game)
     */
    public function test_update_game_allows_null_scores_to_cancel(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $game = Game::first();
        $game->update(['home_score' => 2, 'away_score' => 1, 'played' => true]);
        // Standings will be updated via updateGame method
        $this->leagueManager->updateGame($game->id, [
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ]);

        $response = $this->putJson("/api/league/matches/{$game->id}", [
            'home_score' => null,
            'away_score' => null,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $game->refresh();
        $this->assertNull($game->home_score);
        $this->assertNull($game->away_score);
        $this->assertFalse($game->played);
    }

    /**
     * Test PUT /api/league/matches/{id} returns 404 for non-existent game
     */
    public function test_update_game_returns_404_for_nonexistent_game(): void
    {
        $response = $this->putJson('/api/league/matches/99999', [
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'error',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('NOT_FOUND', $response->json('error'));
    }

    /**
     * Test PUT /api/league/matches/{id} updates standings correctly
     */
    public function test_update_game_updates_standings_correctly(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $game = Game::first();
        $homeTeam = $game->homeTeam;
        $awayTeam = $game->awayTeam;

        // Update game with win for home team
        $response = $this->putJson("/api/league/matches/{$game->id}", [
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $homeStanding = LeagueStanding::where('team_id', $homeTeam->id)->first();
        $awayStanding = LeagueStanding::where('team_id', $awayTeam->id)->first();

        // Home team should have 3 points (win)
        $this->assertEquals(3, $homeStanding->points);
        $this->assertEquals(1, $homeStanding->won);
        $this->assertEquals(2, $homeStanding->goals_for);
        $this->assertEquals(1, $homeStanding->goals_against);

        // Away team should have 0 points (loss)
        $this->assertEquals(0, $awayStanding->points);
        $this->assertEquals(1, $awayStanding->lost);
    }

    /**
     * Test POST /api/league/reset endpoint
     */
    public function test_reset_league_resets_all_games(): void
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

        $response = $this->postJson('/api/league/reset');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('League reset successfully', $response->json('message'));

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
            $this->assertEquals(0, $standing->points);
            $this->assertEquals(0, $standing->played);
        }
    }

    /**
     * Test GET /api/league/predictions endpoint before week 4
     */
    public function test_get_predictions_before_week_4_returns_message(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // Simulate week 1 only
        $this->leagueManager->simulateWeek(1);

        $response = $this->getJson('/api/league/predictions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('Predictions available after week 4', $response->json('message'));
    }

    /**
     * Test GET /api/league/predictions endpoint at week 4
     */
    public function test_get_predictions_at_week_4_returns_predictions(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        // Simulate weeks 1-4
        for ($week = 1; $week <= 4; $week++) {
            $this->leagueManager->simulateWeek($week);
        }

        $response = $this->getJson('/api/league/predictions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'team_id',
                    'team_name',
                    'current_points',
                    'projected_points',
                    'probability',
                ],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertCount(4, $data); // 4 teams
    }

    /**
     * Test GET /api/league/predictions endpoint when no games played
     */
    public function test_get_predictions_when_no_games_played(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $response = $this->getJson('/api/league/predictions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('No games played yet', $response->json('message'));
    }

    /**
     * Test that updating game maintains standings consistency
     */
    public function test_updating_multiple_games_maintains_consistency(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();
        $this->leagueManager->initializeStandings();

        $games = Game::limit(3)->get();

        // Update multiple games
        foreach ($games as $game) {
            $this->putJson("/api/league/matches/{$game->id}", [
                'home_score' => 2,
                'away_score' => 1,
            ]);
        }

        // Check standings consistency
        foreach ($teams as $team) {
            $standing = LeagueStanding::where('team_id', $team->id)->first();
            
            // No negative values (except goal_difference which can be negative)
            $this->assertGreaterThanOrEqual(0, $standing->played);
            $this->assertGreaterThanOrEqual(0, $standing->won);
            $this->assertGreaterThanOrEqual(0, $standing->drawn);
            $this->assertGreaterThanOrEqual(0, $standing->lost);
            $this->assertGreaterThanOrEqual(0, $standing->goals_for);
            $this->assertGreaterThanOrEqual(0, $standing->goals_against);
            $this->assertGreaterThanOrEqual(0, $standing->points);

            // Goal difference should match
            $expectedGD = $standing->goals_for - $standing->goals_against;
            $this->assertEquals($expectedGD, $standing->goal_difference);
        }
    }

    /**
     * Test POST /api/league/generate-fixtures endpoint
     */
    public function test_generate_fixtures_creates_fixtures(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $response = $this->postJson('/api/league/generate-fixtures');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'fixtures_count',
                'weeks',
                'team_count',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertEquals(12, $data['fixtures_count']); // 4 teams * 3 opponents * 2 legs
        $this->assertEquals(6, $data['weeks']); // 6 weeks for 4 teams
        $this->assertEquals(4, $data['team_count']);

        // Verify fixtures were created
        $this->assertEquals(12, Game::count());
    }

    /**
     * Test POST /api/league/generate-fixtures requires minimum teams
     */
    public function test_generate_fixtures_requires_minimum_teams(): void
    {
        // No teams created
        $response = $this->postJson('/api/league/generate-fixtures');

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'success',
            'message',
            'error',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('INSUFFICIENT_TEAMS', $response->json('error'));

        // Only 1 team
        Team::create(['name' => 'Chelsea', 'power' => 90]);

        $response = $this->postJson('/api/league/generate-fixtures');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    /**
     * Test GET /api/league/teams endpoint
     */
    public function test_get_teams_returns_all_teams(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $response = $this->getJson('/api/league/teams');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'power',
                    'logo',
                ],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertCount(4, $data);
        $this->assertEquals('Chelsea', $data[0]['name']);
        $this->assertEquals(90, $data[0]['power']);
    }

    /**
     * Test GET /api/league/matches returns grouped by week
     */
    public function test_get_matches_returns_grouped_by_week(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $response = $this->getJson('/api/league/matches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'weeks' => [
                    '*' => [
                        'week',
                        'matches' => [
                            '*' => [
                                'id',
                                'home_team_id',
                                'away_team_id',
                                'home_team',
                                'away_team',
                                'home_score',
                                'away_score',
                                'played',
                                'week',
                            ],
                        ],
                    ],
                ],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $weeks = $response->json('data.weeks');
        $this->assertCount(6, $weeks); // 6 weeks for 4 teams

        // Check first week has 2 matches
        $this->assertCount(2, $weeks[0]['matches']);
        $this->assertEquals(1, $weeks[0]['week']);
    }

    /**
     * Test GET /api/league/matches returns empty when no fixtures
     */
    public function test_get_matches_returns_empty_when_no_fixtures(): void
    {
        $response = $this->getJson('/api/league/matches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'weeks',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $weeks = $response->json('data.weeks');
        $this->assertIsArray($weeks);
        $this->assertEmpty($weeks);
    }

    /**
     * Test GET /api/league/current-week returns correct info
     */
    public function test_get_current_week_returns_correct_info(): void
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
        $this->leagueManager->simulateWeek(1);

        $response = $this->getJson('/api/league/current-week');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'last_played_week',
                'next_week',
                'total_weeks',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertEquals(1, $data['last_played_week']);
        $this->assertEquals(2, $data['next_week']);
        $this->assertEquals(6, $data['total_weeks']);
    }

    /**
     * Test GET /api/league/current-week handles no games
     */
    public function test_get_current_week_handles_no_games(): void
    {
        $response = $this->getJson('/api/league/current-week');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'last_played_week',
                'next_week',
                'total_weeks',
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertNull($data['last_played_week']);
        $this->assertNull($data['next_week']);
        $this->assertEquals(0, $data['total_weeks']);
    }
}
