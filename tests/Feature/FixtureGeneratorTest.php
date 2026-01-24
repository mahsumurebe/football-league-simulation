<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private FixtureGeneratorService $fixtureGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureGenerator = new FixtureGeneratorService();
    }

    /**
     * Test that exactly 4 teams are required (PDF requirement)
     */
    public function test_requires_at_least_two_teams(): void
    {
        // Test with 0 teams
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('At least two teams required to generate fixtures');
        $this->fixtureGenerator->generateFixtures();

        // Test with 1 team
        Team::create(['name' => 'Team 1', 'power' => 80]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('At least two teams required to generate fixtures');
        $this->fixtureGenerator->generateFixtures();
    }

    /**
     * Test that 4 teams generate correct fixtures (PDF requirement: 4 teams)
     */
    public function test_generates_fixtures_for_four_teams(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        // PDF requirement: Total 12 matches (4 teams * 3 opponents * 2 legs)
        $this->assertEquals(12, Game::count());
    }

    /**
     * Test that each team plays exactly 6 matches (PDF requirement: round-robin)
     */
    public function test_each_team_plays_six_matches(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        foreach ($teams as $team) {
            $homeGames = Game::where('home_team_id', $team->id)->count();
            $awayGames = Game::where('away_team_id', $team->id)->count();
            $totalGames = $homeGames + $awayGames;

            // PDF requirement: Each team plays 6 matches (3 opponents * 2 legs)
            $this->assertEquals(6, $totalGames, "Team {$team->name} should play exactly 6 matches");
        }
    }

    /**
     * Test that each team plays every other team home and away (PDF requirement: round-robin)
     */
    public function test_each_team_plays_every_other_team_home_and_away(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        foreach ($teams as $team) {
            foreach ($teams as $opponent) {
                if ($team->id === $opponent->id) {
                    continue;
                }

                // Check home game
                $homeGame = Game::where('home_team_id', $team->id)
                    ->where('away_team_id', $opponent->id)
                    ->exists();
                $this->assertTrue($homeGame, "{$team->name} should play {$opponent->name} at home");

                // Check away game
                $awayGame = Game::where('home_team_id', $opponent->id)
                    ->where('away_team_id', $team->id)
                    ->exists();
                $this->assertTrue($awayGame, "{$team->name} should play {$opponent->name} away");
            }
        }
    }

    /**
     * Test that there are exactly 6 weeks (PDF requirement: 4 teams = 6 weeks)
     */
    public function test_generates_six_weeks(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $weeks = Game::distinct()->pluck('week')->sort()->values();
        $this->assertEquals([1, 2, 3, 4, 5, 6], $weeks->toArray(), 'Should have exactly 6 weeks');
        $this->assertEquals(6, $weeks->count());
    }

    /**
     * Test that each week has exactly 2 matches (PDF requirement: 4 teams / 2 = 2 matches per week)
     */
    public function test_each_week_has_two_matches(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        for ($week = 1; $week <= 6; $week++) {
            $matchesInWeek = Game::where('week', $week)->count();
            // PDF requirement: 4 teams = 2 matches per week
            $this->assertEquals(2, $matchesInWeek, "Week {$week} should have exactly 2 matches");
        }
    }

    /**
     * Test that no team plays itself
     */
    public function test_no_team_plays_itself(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $selfMatches = Game::whereColumn('home_team_id', 'away_team_id')->count();
        $this->assertEquals(0, $selfMatches, 'No team should play itself');
    }

    /**
     * Test that all games are initially not played
     */
    public function test_all_games_initially_not_played(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        $playedGames = Game::where('played', true)->count();
        $this->assertEquals(0, $playedGames, 'All games should initially be not played');

        $allGames = Game::all();
        foreach ($allGames as $game) {
            $this->assertFalse($game->played);
            $this->assertNull($game->home_score);
            $this->assertNull($game->away_score);
        }
    }

    /**
     * Test that fixtures are cleared before generating new ones
     */
    public function test_clears_existing_fixtures_before_generating(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        // Create some existing games
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1,
            'played' => false,
        ]);
        Game::create([
            'home_team_id' => $teams[2]->id,
            'away_team_id' => $teams[3]->id,
            'week' => 1,
            'played' => false,
        ]);
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[2]->id,
            'week' => 2,
            'played' => false,
        ]);
        Game::create([
            'home_team_id' => $teams[1]->id,
            'away_team_id' => $teams[3]->id,
            'week' => 2,
            'played' => false,
        ]);
        Game::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[3]->id,
            'week' => 3,
            'played' => false,
        ]);

        $this->fixtureGenerator->generateFixtures();

        // Should have exactly 12 games (not 5 + 12)
        $this->assertEquals(12, Game::count());
    }

    /**
     * Test that each team plays exactly once per week (no team plays twice in same week)
     */
    public function test_no_team_plays_twice_in_same_week(): void
    {
        $teams = [
            Team::create(['name' => 'Chelsea', 'power' => 90]),
            Team::create(['name' => 'Arsenal', 'power' => 85]),
            Team::create(['name' => 'Manchester City', 'power' => 88]),
            Team::create(['name' => 'Liverpool', 'power' => 82]),
        ];

        $this->fixtureGenerator->generateFixtures();

        foreach ($teams as $team) {
            for ($week = 1; $week <= 6; $week++) {
                $gamesInWeek = Game::where('week', $week)
                    ->where(function ($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                            ->orWhere('away_team_id', $team->id);
                    })
                    ->count();

                // Each team should play exactly once per week
                $this->assertEquals(1, $gamesInWeek, "Team {$team->name} should play exactly once in week {$week}");
            }
        }
    }
}
