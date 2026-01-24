<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGameRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Game;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\LeagueManagerService;
use App\Services\PredictionEngineService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class LeagueController extends Controller
{
    use ApiResponse;
    private LeagueManagerService $leagueManager;

    private PredictionEngineService $predictionEngine;

    private FixtureGeneratorService $fixtureGenerator;

    public function __construct(
        LeagueManagerService $leagueManager,
        PredictionEngineService $predictionEngine,
        FixtureGeneratorService $fixtureGenerator
    ) {
        $this->leagueManager = $leagueManager;
        $this->predictionEngine = $predictionEngine;
        $this->fixtureGenerator = $fixtureGenerator;
    }

    /**
     * Generate fixtures for existing teams
     */
    #[OA\Post(
        path: '/league/generate-fixtures',
        summary: 'Generate fixtures for existing teams',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fixtures generated successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Not enough teams (minimum 2 required)'
            ),
        ]
    )]
    public function generateFixtures(): JsonResponse
    {
        try {
            $teamCount = Team::count();

            if ($teamCount < 2) {
                return $this->errorResponse(
                    'At least two teams required to generate fixtures',
                    'INSUFFICIENT_TEAMS',
                    400
                );
            }

            $this->fixtureGenerator->generateFixtures();
            $this->leagueManager->initializeStandings();

            $fixturesCount = Game::count();
            $totalWeeks = Game::max('week') ?? 0;

            return $this->successResponse([
                'fixtures_count' => $fixturesCount,
                'weeks' => $totalWeeks,
                'team_count' => $teamCount,
            ], 'Fixtures generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to generate fixtures: ' . $e->getMessage());
        }
    }

    /**
     * Get all teams
     */
    #[OA\Get(
        path: '/league/teams',
        summary: 'Get all teams',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Teams retrieved successfully'
            ),
        ]
    )]
    public function getTeams(): JsonResponse
    {
        $teams = Team::all()->map(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'power' => $team->power,
                'logo' => $team->logo,
            ];
        });

        return $this->successResponse($teams, 'Teams retrieved successfully');
    }

    /**
     * Get all matches grouped by week
     */
    #[OA\Get(
        path: '/league/matches',
        summary: 'Get all matches grouped by week',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matches retrieved successfully'
            ),
        ]
    )]
    public function getMatches(): JsonResponse
    {
        $games = Game::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $weeks = $games->groupBy('week')->map(function ($weekGames, $week) {
            return [
                'week' => (int) $week,
                'matches' => $weekGames->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'home_team_id' => $game->home_team_id,
                        'away_team_id' => $game->away_team_id,
                        'home_team' => $game->homeTeam?->name ?? 'Unknown',
                        'away_team' => $game->awayTeam?->name ?? 'Unknown',
                        'home_score' => $game->home_score,
                        'away_score' => $game->away_score,
                        'played' => $game->played,
                        'week' => $game->week,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return $this->successResponse(['weeks' => $weeks], 'Matches retrieved successfully');
    }

    /**
     * Get current week information
     */
    #[OA\Get(
        path: '/league/current-week',
        summary: 'Get current week information',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current week information retrieved successfully'
            ),
        ]
    )]
    public function getCurrentWeek(): JsonResponse
    {
        $lastPlayedWeek = Game::played()->max('week');
        $nextWeek = Game::notPlayed()->min('week');
        $totalWeeks = Game::max('week') ?? 0;

        return $this->successResponse([
            'last_played_week' => $lastPlayedWeek,
            'next_week' => $nextWeek,
            'total_weeks' => $totalWeeks,
        ], 'Current week information retrieved successfully');
    }

    /**
     * Get current league table
     */
    #[OA\Get(
        path: '/league/table',
        summary: 'Get current league table',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'League table retrieved successfully'
            ),
        ]
    )]
    public function getLeagueTable(): JsonResponse
    {
        $table = $this->leagueManager->getLeagueTable();

        return $this->successResponse($table, 'League table retrieved successfully');
    }

    /**
     * Simulate next week's games
     */
    #[OA\Post(
        path: '/league/simulate-week',
        summary: "Simulate next week's games",
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Week simulated successfully'
            ),
        ]
    )]
    public function simulateNextWeek(): JsonResponse
    {
        try {
            $nextWeek = Game::notPlayed()->min('week');

            if (! $nextWeek) {
                return $this->successResponse(null, 'All games completed');
            }

            $results = $this->leagueManager->simulateWeek($nextWeek);
            $table = $this->leagueManager->getLeagueTable();

            $predictions = [];
            if ($nextWeek >= 4) {
                $predictions = $this->predictionEngine->calculatePredictions($nextWeek);
            }

            return $this->successResponse([
                'week' => $nextWeek,
                'results' => $results,
                'table' => $table,
                'predictions' => $predictions,
            ], 'Week simulated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to simulate week: ' . $e->getMessage());
        }
    }

    /**
     * Simulate all remaining games
     */
    #[OA\Post(
        path: '/league/simulate-all',
        summary: 'Simulate all remaining games',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All games simulated successfully'
            ),
        ]
    )]
    public function simulateAll(): JsonResponse
    {
        try {
            $results = $this->leagueManager->simulateAll();
            $table = $this->leagueManager->getLeagueTable();

            $currentWeek = Game::played()->max('week') ?? 0;
            $predictions = [];

            if ($currentWeek >= 4) {
                $predictions = $this->predictionEngine->calculatePredictions($currentWeek);
            }

            return $this->successResponse([
                'results' => $results,
                'table' => $table,
                'predictions' => $predictions,
            ], 'All games simulated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to simulate all games: ' . $e->getMessage());
        }
    }

    /**
     * Get game results by week
     */
    #[OA\Get(
        path: '/league/matches/week/{week}',
        summary: 'Get matches by week',
        tags: ['League'],
        parameters: [
            new OA\Parameter(
                name: 'week',
                in: 'path',
                required: true,
                description: 'Week number',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matches retrieved successfully'
            ),
        ]
    )]
    public function getMatchesByWeek(int $week): JsonResponse
    {
        if ($week < 1) {
            return $this->errorResponse('Invalid week number', 'INVALID_WEEK', 400);
        }

        $games = Game::where('week', $week)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        if ($games->isEmpty()) {
            return $this->notFoundResponse('No matches found for week ' . $week);
        }

        $matches = $games->map(function ($game) {
            return [
                'home_team' => $game->homeTeam?->name ?? 'Unknown',
                'away_team' => $game->awayTeam?->name ?? 'Unknown',
                'score' => $game->played
                    ? "{$game->home_score} - {$game->away_score}"
                    : 'Not played',
                'played' => $game->played,
            ];
        });

        return $this->successResponse($matches, 'Matches retrieved successfully');
    }

    /**
     * Reset league (for testing)
     */
    #[OA\Post(
        path: '/league/reset',
        summary: 'Reset league',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'League reset successfully'
            ),
        ]
    )]
    public function resetLeague(): JsonResponse
    {
        try {
            $this->leagueManager->resetLeague();

            return $this->successResponse(null, 'League reset successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to reset league: ' . $e->getMessage());
        }
    }

    /**
     * Get current week predictions
     */
    #[OA\Get(
        path: '/league/predictions',
        summary: 'Get current week predictions',
        tags: ['League'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Predictions retrieved successfully'
            ),
        ]
    )]
    public function getPredictions(): JsonResponse
    {
        $currentWeek = Game::played()->max('week') ?? 0;

        if ($currentWeek < 4) {
            if ($currentWeek === 0) {
                return $this->successResponse(
                    null,
                    'No games played yet. Predictions will be available after week 4'
                );
            }
            return $this->successResponse(
                null,
                'Predictions available after week 4. Current week: ' . $currentWeek
            );
        }

        $predictions = $this->predictionEngine->calculatePredictions($currentWeek);

        return $this->successResponse($predictions, 'Predictions retrieved successfully');
    }

    /**
     * Update a game's scores
     */
    #[OA\Put(
        path: '/league/matches/{id}',
        summary: 'Update a game\'s scores',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'home_score',
                        type: 'integer',
                        nullable: true,
                        description: 'Home team score (nullable to mark game as not played)',
                        example: 2
                    ),
                    new OA\Property(
                        property: 'away_score',
                        type: 'integer',
                        nullable: true,
                        description: 'Away team score (nullable to mark game as not played)',
                        example: 1
                    ),
                ]
            )
        ),
        tags: ['League'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Game ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Game updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Game updated successfully'),
                        new OA\Property(
                            property: 'game',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'home_team', type: 'string'),
                                new OA\Property(property: 'away_team', type: 'string'),
                                new OA\Property(property: 'home_score', type: 'integer', nullable: true),
                                new OA\Property(property: 'away_score', type: 'integer', nullable: true),
                                new OA\Property(property: 'week', type: 'integer'),
                                new OA\Property(property: 'played', type: 'boolean'),
                            ]
                        ),
                        new OA\Property(
                            property: 'table',
                            type: 'array',
                            description: 'Updated league table',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'team_id', type: 'integer'),
                                    new OA\Property(property: 'played', type: 'integer'),
                                    new OA\Property(property: 'won', type: 'integer'),
                                    new OA\Property(property: 'drawn', type: 'integer'),
                                    new OA\Property(property: 'lost', type: 'integer'),
                                    new OA\Property(property: 'goals_for', type: 'integer'),
                                    new OA\Property(property: 'goals_against', type: 'integer'),
                                    new OA\Property(property: 'goal_difference', type: 'integer'),
                                    new OA\Property(property: 'points', type: 'integer'),
                                    new OA\Property(
                                        property: 'team',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'name', type: 'string'),
                                            new OA\Property(property: 'power', type: 'integer'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Game not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function updateGame(int $id, UpdateGameRequest $request): JsonResponse
    {
        try {
            $game = $this->leagueManager->updateGame($id, $request->validated());
            $table = $this->leagueManager->getLeagueTable();

            return $this->successResponse([
                'game' => [
                    'id' => $game->id,
                    'home_team' => $game->homeTeam->name,
                    'away_team' => $game->awayTeam->name,
                    'home_score' => $game->home_score,
                    'away_score' => $game->away_score,
                    'week' => $game->week,
                    'played' => $game->played,
                ],
                'table' => $table,
            ], 'Game updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Game not found');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update game: ' . $e->getMessage());
        }
    }
}
