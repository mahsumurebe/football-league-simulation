Match = Game
Matches = Games

# 🏆 FOOTBALL LEAGUE SIMULATION - COMPLETE PROJECT SETUP

## 📋 PROJECT OVERVIEW

Build a **Football League Simulation System** where 4 teams compete in a round-robin Premier League format. The system simulates matches week by week, calculates league standings, and predicts championship probabilities after week 4.

**Timeline:** 2 days  
**Tech Stack:** Laravel 10+ (PHP 8.1+), Vue.js 3, MySQL, Tailwind CSS  
**Deployment:** GitHub + Live demo required

---

## 🎯 CORE REQUIREMENTS

### Functional Requirements:
1. ✅ 4 teams with different power ratings (e.g., Chelsea: 90, Arsenal: 85, Man City: 88, Liverpool: 82)
2. ✅ Round-robin fixture generation (each team plays every other team home and away = 6 matches per team, 12 total matches)
3. ✅ Week-by-week match simulation with realistic results
4. ✅ Premier League scoring system (Win: 3pts, Draw: 1pt, Loss: 0pts)
5. ✅ League table with: Points, Played, Won, Drawn, Lost, Goals For, Goals Against, Goal Difference
6. ✅ Championship probability prediction starting from Week 4
7. ✅ "Play All" button to simulate entire season automatically
8. ✅ Match result display by weeks

### Technical Requirements:
- ✅ **OOP principles** (SOLID, Service Layer Pattern)
- ✅ **Laravel** backend with RESTful API
- ✅ **Vue.js** frontend (reactive, component-based)
- ✅ **Tailwind CSS** for styling
- ✅ **Unit tests** (bonus points)
- ✅ GitHub repository with proper README
- ✅ Clean commit history

---

## 🚀 STEP 1: PROJECT INITIALIZATION

### 1.1 Create Laravel Project
```bash
composer create-project laravel/laravel football-league-simulation
cd football-league-simulation
```

### 1.2 Install Dependencies
```bash
# Backend dependencies
composer require --dev barryvdh/laravel-debugbar
composer require --dev phpunit/phpunit

# Frontend dependencies
npm install
npm install vue@next @vitejs/plugin-vue
npm install -D tailwindcss@latest postcss@latest autoprefixer@latest
npx tailwindcss init -p
```

### 1.3 Configure Vite for Vue.js

**File: `vite.config.js`**
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
```

### 1.4 Configure Tailwind CSS

**File: `tailwind.config.js`**
```javascript
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

**File: `resources/css/app.css`**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 1.5 Configure Database

**File: `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=football_league
DB_USERNAME=root
DB_PASSWORD=
```

Create database:
```bash
mysql -u root -p
CREATE DATABASE football_league;
exit;
```

---

## 📊 STEP 2: DATABASE SCHEMA & MIGRATIONS

### 2.1 Create Migrations
```bash
php artisan make:migration create_teams_table
php artisan make:migration create_matches_table
php artisan make:migration create_league_standings_table
php artisan make:migration create_championship_predictions_table
```

### 2.2 Teams Migration

**File: `database/migrations/xxxx_create_teams_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('power')->default(50); // Team strength 1-100
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
```

### 2.3 Matches Migration

**File: `database/migrations/xxxx_create_matches_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('week'); // Match week (1-6)
            $table->boolean('played')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
```

### 2.4 League Standings Migration

**File: `database/migrations/xxxx_create_league_standings_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('played')->default(0);
            $table->integer('won')->default(0);
            $table->integer('drawn')->default(0);
            $table->integer('lost')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_standings');
    }
};
```

### 2.5 Championship Predictions Migration

**File: `database/migrations/xxxx_create_championship_predictions_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('week');
            $table->decimal('probability', 5, 2); // 0.00 to 100.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_predictions');
    }
};
```

### 2.6 Run Migrations
```bash
php artisan migrate
```

---

## 🏗️ STEP 3: ELOQUENT MODELS

### 3.1 Team Model
```bash
php artisan make:model Team
```

**File: `app/Models/Team.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'power', 'logo'];

    public function homeMatches(): HasMany
    {
        return $this->hasMany(Match::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(Match::class, 'away_team_id');
    }

    public function standing(): HasOne
    {
        return $this->hasOne(LeagueStanding::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(ChampionshipPrediction::class);
    }
}
```

### 3.2 Match Model
```bash
php artisan make:model Match
```

**File: `app/Models/Match.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Match extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'played'
    ];

    protected $casts = [
        'played' => 'boolean',
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function scopeByWeek($query, int $week)
    {
        return $query->where('week', $week);
    }

    public function scopePlayed($query)
    {
        return $query->where('played', true);
    }

    public function scopeNotPlayed($query)
    {
        return $query->where('played', false);
    }
}
```

### 3.3 LeagueStanding Model
```bash
php artisan make:model LeagueStanding
```

**File: `app/Models/LeagueStanding.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

### 3.4 ChampionshipPrediction Model
```bash
php artisan make:model ChampionshipPrediction
```

**File: `app/Models/ChampionshipPrediction.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipPrediction extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'week', 'probability'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

---

## 🔧 STEP 4: SERVICE LAYER (CRITICAL - OOP DESIGN)

### 4.1 FixtureGeneratorService

```bash
mkdir -p app/Services
```

**File: `app/Services/FixtureGeneratorService.php`**
```php
<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Match;

class FixtureGeneratorService
{
    /**
     * Generate round-robin fixtures for all teams
     * Each team plays every other team twice (home and away)
     */
    public function generateFixtures(): void
    {
        // Clear existing matches
        Match::truncate();

        $teams = Team::all();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            throw new \Exception('At least 2 teams required to generate fixtures');
        }

        $week = 1;
        $fixtures = [];

        // Round-robin algorithm
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

        // Return fixtures (reverse for away games)
        foreach ($fixtures as $fixture) {
            $fixtures[] = [
                'home_team_id' => $fixture['away_team_id'],
                'away_team_id' => $fixture['home_team_id'],
                'week' => $week++,
                'played' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Match::insert($fixtures);
    }
}
```

### 4.2 MatchSimulatorService

**File: `app/Services/MatchSimulatorService.php`**
```php
<?php

namespace App\Services;

use App\Models\Match;
use App\Models\Team;

class MatchSimulatorService
{
    private const HOME_ADVANTAGE = 5; // Home team power bonus
    private const BASE_DRAW_CHANCE = 20; // Base percentage for draws

    /**
     * Simulate a single match based on team powers
     */
    public function simulateMatch(Match $match): array
    {
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        // Calculate adjusted strengths
        $homeStrength = $homeTeam->power + self::HOME_ADVANTAGE;
        $awayStrength = $awayTeam->power;

        // Calculate win probabilities
        $totalStrength = $homeStrength + $awayStrength;
        $homeWinChance = ($homeStrength / $totalStrength) * 100;
        $awayWinChance = ($awayStrength / $totalStrength) * 100;

        // Adjust for draw chance
        $homeWinChance = $homeWinChance - (self::BASE_DRAW_CHANCE / 2);
        $awayWinChance = $awayWinChance - (self::BASE_DRAW_CHANCE / 2);

        // Generate random outcome
        $random = mt_rand(1, 100);

        if ($random <= $homeWinChance) {
            // Home team wins
            return $this->generateScore($homeTeam, $awayTeam, 'home_win');
        } elseif ($random <= ($homeWinChance + self::BASE_DRAW_CHANCE)) {
            // Draw
            return $this->generateScore($homeTeam, $awayTeam, 'draw');
        } else {
            // Away team wins
            return $this->generateScore($homeTeam, $awayTeam, 'away_win');
        }
    }

    /**
     * Generate realistic score based on match outcome
     */
    private function generateScore(Team $homeTeam, Team $awayTeam, string $outcome): array
    {
        switch ($outcome) {
            case 'home_win':
                // Home team wins: 1-0, 2-0, 2-1, 3-1, 3-0
                $homeScore = mt_rand(1, 3);
                $awayScore = max(0, $homeScore - mt_rand(1, 2));
                break;

            case 'draw':
                // Draw: 0-0, 1-1, 2-2
                $score = mt_rand(0, 2);
                $homeScore = $score;
                $awayScore = $score;
                break;

            case 'away_win':
                // Away team wins: 0-1, 0-2, 1-2, 1-3
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
```

### 4.3 LeagueManagerService

**File: `app/Services/LeagueManagerService.php`**
```php
<?php

namespace App\Services;

use App\Models\Match;
use App\Models\LeagueStanding;
use App\Models\Team;

class LeagueManagerService
{
    private MatchSimulatorService $matchSimulator;

    public function __construct(MatchSimulatorService $matchSimulator)
    {
        $this->matchSimulator = $matchSimulator;
    }

    /**
     * Initialize league standings for all teams
     */
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

    /**
     * Simulate matches for a specific week
     */
    public function simulateWeek(int $week): array
    {
        $matches = Match::byWeek($week)->notPlayed()->with(['homeTeam', 'awayTeam'])->get();

        $results = [];

        foreach ($matches as $match) {
            $score = $this->matchSimulator->simulateMatch($match);

            $match->update([
                'home_score' => $score['home_score'],
                'away_score' => $score['away_score'],
                'played' => true,
            ]);

            $this->updateStandings($match);

            $results[] = [
                'match_id' => $match->id,
                'home_team' => $match->homeTeam->name,
                'away_team' => $match->awayTeam->name,
                'score' => "{$score['home_score']} - {$score['away_score']}",
            ];
        }

        return $results;
    }

    /**
     * Simulate all remaining matches
     */
    public function simulateAll(): array
    {
        $allResults = [];
        $currentWeek = Match::notPlayed()->min('week') ?? 1;
        $maxWeek = Match::max('week');

        for ($week = $currentWeek; $week <= $maxWeek; $week++) {
            $weekResults = $this->simulateWeek($week);
            $allResults["Week {$week}"] = $weekResults;
        }

        return $allResults;
    }

    /**
     * Update league standings after a match
     */
    private function updateStandings(Match $match): void
    {
        $homeStanding = LeagueStanding::where('team_id', $match->home_team_id)->first();
        $awayStanding = LeagueStanding::where('team_id', $match->away_team_id)->first();

        // Update home team
        $homeStanding->played++;
        $homeStanding->goals_for += $match->home_score;
        $homeStanding->goals_against += $match->away_score;

        if ($match->home_score > $match->away_score) {
            // Home win
            $homeStanding->won++;
            $homeStanding->points += 3;
        } elseif ($match->home_score == $match->away_score) {
            // Draw
            $homeStanding->drawn++;
            $homeStanding->points += 1;
        } else {
            // Home loss
            $homeStanding->lost++;
        }

        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $homeStanding->save();

        // Update away team
        $awayStanding->played++;
        $awayStanding->goals_for += $match->away_score;
        $awayStanding->goals_against += $match->home_score;

        if ($match->away_score > $match->home_score) {
            // Away win
            $awayStanding->won++;
            $awayStanding->points += 3;
        } elseif ($match->away_score == $match->home_score) {
            // Draw
            $awayStanding->drawn++;
            $awayStanding->points += 1;
        } else {
            // Away loss
            $awayStanding->lost++;
        }

        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;
        $awayStanding->save();
    }

    /**
     * Get current league table sorted by points
     */
    public function getLeagueTable(): array
    {
        return LeagueStanding::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Reset entire league (for testing purposes)
     */
    public function resetLeague(): void
    {
        Match::query()->update([
            'home_score' => null,
            'away_score' => null,
            'played' => false,
        ]);

        $this->initializeStandings();
    }
}
```

### 4.4 PredictionEngineService

**File: `app/Services/PredictionEngineService.php`**
```php
<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Match;
use App\Models\LeagueStanding;
use App\Models\ChampionshipPrediction;

class PredictionEngineService
{
    /**
     * Calculate championship probabilities for all teams
     * Should be called after week 4
     */
    public function calculatePredictions(int $currentWeek): array
    {
        if ($currentWeek < 4) {
            return ['error' => 'Predictions available after week 4'];
        }

        $standings = LeagueStanding::with('team')->orderBy('points', 'desc')->get();
        $teams = Team::all();
        $maxWeek = Match::max('week');
        $remainingWeeks = $maxWeek - $currentWeek;

        $predictions = [];

        foreach ($teams as $team) {
            $standing = $standings->where('team_id', $team->id)->first();
            
            // Calculate base probability from current position
            $currentPoints = $standing->points;
            $maxPossiblePoints = $currentPoints + ($remainingWeeks * 3); // 3 points per win

            // Get remaining matches for this team
            $remainingMatches = Match::where(function ($query) use ($team) {
                $query->where('home_team_id', $team->id)
                      ->orWhere('away_team_id', $team->id);
            })
            ->where('played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

            // Calculate expected points from remaining matches
            $expectedPoints = 0;
            foreach ($remainingMatches as $match) {
                $isHome = $match->home_team_id == $team->id;
                $opponent = $isHome ? $match->awayTeam : $match->homeTeam;
                
                $winProbability = $this->calculateWinProbability($team, $opponent, $isHome);
                $expectedPoints += ($winProbability * 3) + ((1 - $winProbability) * 0.5); // Win=3pts, Draw=1pt avg
            }

            $projectedPoints = $currentPoints + $expectedPoints;

            // Calculate probability based on projected points and current position
            $probability = $this->calculateChampionshipProbability(
                $projectedPoints,
                $standings,
                $team->id
            );

            $predictions[] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'current_points' => $currentPoints,
                'projected_points' => round($projectedPoints, 1),
                'probability' => round($probability, 2),
            ];

            // Save to database
            ChampionshipPrediction::updateOrCreate(
                ['team_id' => $team->id, 'week' => $currentWeek],
                ['probability' => round($probability, 2)]
            );
        }

        // Normalize probabilities to sum to 100%
        $totalProbability = array_sum(array_column($predictions, 'probability'));
        
        foreach ($predictions as &$prediction) {
            $prediction['probability'] = round(($prediction['probability'] / $totalProbability) * 100, 2);
        }

        // Sort by probability
        usort($predictions, function ($a, $b) {
            return $b['probability'] <=> $a['probability'];
        });

        return $predictions;
    }

    /**
     * Calculate win probability between two teams
     */
    private function calculateWinProbability(Team $team, Team $opponent, bool $isHome): float
    {
        $teamPower = $team->power + ($isHome ? 5 : 0); // Home advantage
        $opponentPower = $opponent->power + (!$isHome ? 5 : 0);

        $totalPower = $teamPower + $opponentPower;
        
        return $teamPower / $totalPower;
    }

    /**
     * Calculate championship probability based on projected points
     */
    private function calculateChampionshipProbability(
        float $projectedPoints,
        $standings,
        int $teamId
    ): float {
        $topPoints = $standings->first()->points;
        $bottomPoints = $standings->last()->points;
        $pointsRange = max($topPoints - $bottomPoints, 1);

        // Base probability on relative position
        $relativeProbability = ($projectedPoints - $bottomPoints) / $pointsRange;

        // Apply exponential curve to make it more realistic
        // (leaders get much higher probability)
        return pow($relativeProbability, 2) * 100;
    }
}
```

---

## 🎮 STEP 5: CONTROLLERS & API ROUTES

### 5.1 LeagueController

```bash
php artisan make:controller LeagueController
```

**File: `app/Http/Controllers/LeagueController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Services\LeagueManagerService;
use App\Services\PredictionEngineService;
use App\Models\Match;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    private LeagueManagerService $leagueManager;
    private PredictionEngineService $predictionEngine;

    public function __construct(
        LeagueManagerService $leagueManager,
        PredictionEngineService $predictionEngine
    ) {
        $this->leagueManager = $leagueManager;
        $this->predictionEngine = $predictionEngine;
    }

    /**
     * Get current league table
     */
    public function getLeagueTable(): JsonResponse
    {
        $table = $this->leagueManager->getLeagueTable();
        return response()->json($table);
    }

    /**
     * Simulate next week's matches
     */
    public function simulateNextWeek(): JsonResponse
    {
        $nextWeek = Match::notPlayed()->min('week');

        if (!$nextWeek) {
            return response()->json(['message' => 'All matches completed'], 200);
        }

        $results = $this->leagueManager->simulateWeek($nextWeek);
        $table = $this->leagueManager->getLeagueTable();

        // Calculate predictions if week >= 4
        $predictions = [];
        if ($nextWeek >= 4) {
            $predictions = $this->predictionEngine->calculatePredictions($nextWeek);
        }

        return response()->json([
            'week' => $nextWeek,
            'results' => $results,
            'table' => $table,
            'predictions' => $predictions,
        ]);
    }

    /**
     * Simulate all remaining matches
     */
    public function simulateAll(): JsonResponse
    {
        $results = $this->leagueManager->simulateAll();
        $table = $this->leagueManager->getLeagueTable();

        $currentWeek = Match::played()->max('week') ?? 0;
        $predictions = $this->predictionEngine->calculatePredictions($currentWeek);

        return response()->json([
            'results' => $results,
            'table' => $table,
            'predictions' => $predictions,
        ]);
    }

    /**
     * Get match results by week
     */
    public function getMatchesByWeek(int $week): JsonResponse
    {
        $matches = Match::byWeek($week)
            ->with(['homeTeam', 'awayTeam'])
            ->get()
            ->map(function ($match) {
                return [
                    'home_team' => $match->homeTeam->name,
                    'away_team' => $match->awayTeam->name,
                    'score' => $match->played 
                        ? "{$match->home_score} - {$match->away_score}"
                        : 'Not played',
                    'played' => $match->played,
                ];
            });

        return response()->json($matches);
    }

    /**
     * Reset league (for testing)
     */
    public function resetLeague(): JsonResponse
    {
        $this->leagueManager->resetLeague();

        return response()->json(['message' => 'League reset successfully']);
    }

    /**
     * Get current week predictions
     */
    public function getPredictions(): JsonResponse
    {
        $currentWeek = Match::played()->max('week') ?? 0;

        if ($currentWeek < 4) {
            return response()->json(['message' => 'Predictions available after week 4']);
        }

        $predictions = $this->predictionEngine->calculatePredictions($currentWeek);

        return response()->json($predictions);
    }
}
```

### 5.2 API Routes

**File: `routes/api.php`**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;

Route::prefix('league')->group(function () {
    Route::get('/table', [LeagueController::class, 'getLeagueTable']);
    Route::post('/simulate-week', [LeagueController::class, 'simulateNextWeek']);
    Route::post('/simulate-all', [LeagueController::class, 'simulateAll']);
    Route::get('/matches/week/{week}', [LeagueController::class, 'getMatchesByWeek']);
    Route::post('/reset', [LeagueController::class, 'resetLeague']);
    Route::get('/predictions', [LeagueController::class, 'getPredictions']);
});
```

---

## 🌱 STEP 6: DATABASE SEEDER

### 6.1 Create Team Seeder

```bash
php artisan make:seeder TeamSeeder
```

**File: `database/seeders/TeamSeeder.php`**
```php
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
        // Create 4 teams with different strengths
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
```

**File: `database/seeders/DatabaseSeeder.php`**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TeamSeeder::class,
        ]);
    }
}
```

### 6.2 Run Seeder
```bash
php artisan db:seed
```

---

## 🎨 STEP 7: FRONTEND - VUE.JS COMPONENTS

### 7.1 Setup Vue.js Entry Point

**File: `resources/js/app.js`**
```javascript
import './bootstrap';
import { createApp } from 'vue';
import LeagueSimulation from './components/LeagueSimulation.vue';

createApp({
    components: {
        LeagueSimulation
    }
}).mount('#app');
```

### 7.2 Main Vue Component

**File: `resources/js/components/LeagueSimulation.vue`**
```vue
<template>
  <div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <h1 class="text-4xl font-bold text-center mb-8 text-gray-800">
        ⚽ Football League Simulation
      </h1>

      <!-- Control Buttons -->
      <div class="flex justify-center gap-4 mb-8">
        <button 
          @click="simulateNextWeek"
          :disabled="isLoading"
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition disabled:bg-gray-400"
        >
          Play Next Week
        </button>
        <button 
          @click="simulateAll"
          :disabled="isLoading"
          class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition disabled:bg-gray-400"
        >
          Play All
        </button>
        <button 
          @click="resetLeague"
          :disabled="isLoading"
          class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition disabled:bg-gray-400"
        >
          Reset League
        </button>
      </div>

      <!-- Loading Indicator -->
      <div v-if="isLoading" class="text-center mb-8">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Simulating matches...</p>
      </div>

      <!-- Current Week Display -->
      <div v-if="currentWeek > 0" class="text-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">
          Week {{ currentWeek }} Results
        </h2>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- League Table -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">
            League Table
          </h2>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-4 py-2 text-left">Pos</th>
                  <th class="px-4 py-2 text-left">Team</th>
                  <th class="px-4 py-2 text-center">P</th>
                  <th class="px-4 py-2 text-center">W</th>
                  <th class="px-4 py-2 text-center">D</th>
                  <th class="px-4 py-2 text-center">L</th>
                  <th class="px-4 py-2 text-center">GF</th>
                  <th class="px-4 py-2 text-center">GA</th>
                  <th class="px-4 py-2 text-center">GD</th>
                  <th class="px-4 py-2 text-center font-bold">Pts</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(team, index) in leagueTable" 
                  :key="team.team_id"
                  :class="index === 0 ? 'bg-green-50' : ''"
                  class="border-b hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-center font-semibold">{{ index + 1 }}</td>
                  <td class="px-4 py-3 font-medium">{{ team.team.name }}</td>
                  <td class="px-4 py-3 text-center">{{ team.played }}</td>
                  <td class="px-4 py-3 text-center">{{ team.won }}</td>
                  <td class="px-4 py-3 text-center">{{ team.drawn }}</td>
                  <td class="px-4 py-3 text-center">{{ team.lost }}</td>
                  <td class="px-4 py-3 text-center">{{ team.goals_for }}</td>
                  <td class="px-4 py-3 text-center">{{ team.goals_against }}</td>
                  <td class="px-4 py-3 text-center" :class="team.goal_difference > 0 ? 'text-green-600' : team.goal_difference < 0 ? 'text-red-600' : ''">
                    {{ team.goal_difference > 0 ? '+' : '' }}{{ team.goal_difference }}
                  </td>
                  <td class="px-4 py-3 text-center font-bold text-lg">{{ team.points }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Match Results -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">
            {{ currentWeek > 0 ? `Week ${currentWeek}` : 'Latest' }} Match Results
          </h2>
          <div v-if="weekResults.length > 0" class="space-y-3">
            <div 
              v-for="match in weekResults" 
              :key="match.match_id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
            >
              <div class="flex-1 text-right font-medium">{{ match.home_team }}</div>
              <div class="px-4 text-xl font-bold text-gray-700">{{ match.score }}</div>
              <div class="flex-1 text-left font-medium">{{ match.away_team }}</div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-8">
            No matches played yet. Click "Play Next Week" to start!
          </div>
        </div>
      </div>

      <!-- Championship Predictions (Show after Week 4) -->
      <div v-if="predictions.length > 0" class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">
          🏆 Championship Predictions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div 
            v-for="prediction in predictions" 
            :key="prediction.team_id"
            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4"
          >
            <h3 class="font-bold text-lg mb-2">{{ prediction.team_name }}</h3>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-bold text-blue-600">{{ prediction.probability }}%</span>
              <span class="text-sm text-gray-600">chance</span>
            </div>
            <div class="mt-2 text-sm text-gray-700">
              Current: {{ prediction.current_points }} pts<br>
              Projected: {{ prediction.projected_points }} pts
            </div>
            <div class="mt-2 bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                :style="{ width: prediction.probability + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- All Weeks Results (After Play All) -->
      <div v-if="allWeeksResults.length > 0" class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">
          📅 All Weeks Summary
        </h2>
        <div class="space-y-6">
          <div v-for="(weekData, weekName) in groupedResults" :key="weekName">
            <h3 class="font-bold text-lg mb-2 text-gray-700">{{ weekName }}</h3>
            <div class="space-y-2">
              <div 
                v-for="(match, idx) in weekData" 
                :key="idx"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-1 text-right">{{ match.home_team }}</div>
                <div class="px-4 font-bold">{{ match.score }}</div>
                <div class="flex-1 text-left">{{ match.away_team }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LeagueSimulation',
  data() {
    return {
      leagueTable: [],
      weekResults: [],
      predictions: [],
      allWeeksResults: [],
      currentWeek: 0,
      isLoading: false,
    };
  },
  computed: {
    groupedResults() {
      return this.allWeeksResults;
    }
  },
  mounted() {
    this.fetchLeagueTable();
  },
  methods: {
    async fetchLeagueTable() {
      try {
        const response = await fetch('/api/league/table');
        this.leagueTable = await response.json();
      } catch (error) {
        console.error('Error fetching league table:', error);
      }
    },
    async simulateNextWeek() {
      this.isLoading = true;
      this.allWeeksResults = [];
      
      try {
        const response = await fetch('/api/league/simulate-week', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
        });
        
        const data = await response.json();
        
        if (data.message) {
          alert(data.message);
        } else {
          this.currentWeek = data.week;
          this.weekResults = data.results;
          this.leagueTable = data.table;
          this.predictions = data.predictions || [];
        }
      } catch (error) {
        console.error('Error simulating week:', error);
        alert('Error simulating week');
      } finally {
        this.isLoading = false;
      }
    },
    async simulateAll() {
      this.isLoading = true;
      
      try {
        const response = await fetch('/api/league/simulate-all', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
        });
        
        const data = await response.json();
        
        this.allWeeksResults = data.results;
        this.leagueTable = data.table;
        this.predictions = data.predictions || [];
        this.weekResults = [];
        this.currentWeek = 0;
      } catch (error) {
        console.error('Error simulating all:', error);
        alert('Error simulating all matches');
      } finally {
        this.isLoading = false;
      }
    },
    async resetLeague() {
      if (!confirm('Are you sure you want to reset the league?')) {
        return;
      }
      
      this.isLoading = true;
      
      try {
        await fetch('/api/league/reset', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
        });
        
        this.weekResults = [];
        this.predictions = [];
        this.allWeeksResults = [];
        this.currentWeek = 0;
        
        await this.fetchLeagueTable();
        
        alert('League reset successfully!');
      } catch (error) {
        console.error('Error resetting league:', error);
        alert('Error resetting league');
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>
```

### 7.3 Blade Template

**File: `resources/views/welcome.blade.php`**
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football League Simulation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <league-simulation></league-simulation>
    </div>
</body>
</html>
```

### 7.4 Web Routes

**File: `routes/web.php`**
```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```

---

## 🧪 STEP 8: UNIT TESTS (BONUS)

### 8.1 Test MatchSimulator

```bash
php artisan make:test MatchSimulatorTest
```

**File: `tests/Feature/MatchSimulatorTest.php`**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Match;
use App\Services\MatchSimulatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MatchSimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_strong_team_wins_more_often()
    {
        $strongTeam = Team::create(['name' => 'Strong Team', 'power' => 95]);
        $weakTeam = Team::create(['name' => 'Weak Team', 'power' => 30]);

        $match = Match::create([
            'home_team_id' => $strongTeam->id,
            'away_team_id' => $weakTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        $simulator = new MatchSimulatorService();
        $wins = 0;
        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $simulator->simulateMatch($match);
            if ($result['home_score'] > $result['away_score']) {
                $wins++;
            }
        }

        $winRate = ($wins / $simulations) * 100;

        // Strong team should win at least 70% of the time
        $this->assertGreaterThanOrEqual(70, $winRate);
    }

    public function test_weak_team_can_occasionally_win()
    {
        $strongTeam = Team::create(['name' => 'Strong Team', 'power' => 95]);
        $weakTeam = Team::create(['name' => 'Weak Team', 'power' => 30]);

        $match = Match::create([
            'home_team_id' => $weakTeam->id,
            'away_team_id' => $strongTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        $simulator = new MatchSimulatorService();
        $wins = 0;
        $simulations = 1000;

        for ($i = 0; $i < $simulations; $i++) {
            $result = $simulator->simulateMatch($match);
            if ($result['home_score'] > $result['away_score']) {
                $wins++;
            }
        }

        // Weak team should win at least once in 1000 simulations
        $this->assertGreaterThan(0, $wins);
    }
}
```

### 8.2 Run Tests
```bash
php artisan test
```

---

## 📦 STEP 9: BUILD & DEPLOYMENT PREPARATION

### 9.1 Build Frontend Assets
```bash
npm run build
```

### 9.2 Production Environment Configuration

**File: `.env.production` (Example for server)**
```env
APP_NAME="Football League Simulation"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=football_league_prod
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 9.3 Nginx Configuration (Example)

**File: `/etc/nginx/sites-available/football-league`**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/football-league-simulation/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/football-league /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9.4 Create README.md

**File: `README.md`**
```markdown
# ⚽ Football League Simulation

A Laravel + Vue.js application that simulates a 4-team football league with realistic match outcomes and championship predictions.

## 🚀 Features

- Round-robin league simulation (4 teams)
- Realistic match simulation based on team power ratings
- Week-by-week match progression
- Live league table with Premier League rules
- Championship probability predictions (after week 4)
- "Play All" feature to simulate entire season
- Clean OOP architecture with service layer

## 🛠️ Tech Stack

- **Backend:** Laravel 10+ (PHP 8.1+)
- **Frontend:** Vue.js 3 + Vite
- **Database:** MySQL
- **Styling:** Tailwind CSS

## 📋 Installation

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+
- MySQL

### Steps

1. **Clone repository**
```bash
git clone <your-repo-url>
cd football-league-simulation
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set database credentials:
```env
DB_DATABASE=football_league
DB_USERNAME=root
DB_PASSWORD=
```

5. **Create database**
```bash
mysql -u root -p
CREATE DATABASE football_league;
exit;
```

6. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Build frontend assets**
```bash
npm run build
```

8. **Start development server**
```bash
php artisan serve
```

9. **Visit application**
```
http://localhost:8000
```

## 🎮 Usage

1. **Play Next Week:** Simulates the next week's matches
2. **Play All:** Simulates all remaining matches automatically
3. **Reset League:** Resets the league to initial state

### Championship Predictions
- Predictions appear after Week 4
- Based on current points, remaining fixtures, and team power
- Probabilities are normalized to sum to 100%

## 🧪 Running Tests

```bash
php artisan test
```

## 🏗️ Architecture

### Service Layer
- `FixtureGeneratorService`: Generates round-robin fixtures
- `MatchSimulatorService`: Simulates match outcomes with realistic probabilities
- `LeagueManagerService`: Manages league progression and standings
- `PredictionEngineService`: Calculates championship probabilities

### Models
- `Team`: Football teams with power ratings
- `Match`: Individual matches with scores
- `LeagueStanding`: Current league table positions
- `ChampionshipPrediction`: Probability predictions per week

### API Endpoints
```
GET  /api/league/table              - Get current league table
POST /api/league/simulate-week      - Simulate next week
POST /api/league/simulate-all       - Simulate all matches
GET  /api/league/matches/week/{id}  - Get matches by week
GET  /api/league/predictions        - Get championship predictions
POST /api/league/reset              - Reset league
```

## 📊 Algorithm Details

### Match Simulation
1. Calculate adjusted team powers (home advantage: +5)
2. Determine win probabilities based on power ratio
3. Apply base draw chance (20%)
4. Generate random outcome weighted by probabilities
5. Create realistic score based on outcome

### Championship Prediction
1. Calculate remaining possible points per team
2. Simulate expected points from remaining fixtures
3. Project final points for each team
4. Calculate probabilities using exponential curve
5. Normalize probabilities to sum to 100%

## 📦 Deployment

This application is designed to be deployed to a Linux server via GitHub Actions CI/CD pipeline.

**Server Requirements:**
- Ubuntu 20.04+ / CentOS 8+
- PHP 8.1+, Composer
- Node.js 18+, npm
- MySQL 8.0+
- Nginx or Apache
- Git

**Manual Deployment (Initial Setup):**
```bash
# On your server
cd /var/www
git clone <your-repo-url> football-league-simulation
cd football-league-simulation

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --force
php artisan db:seed --force

# Configure web server (Nginx example)
# Point document root to /var/www/football-league-simulation/public
```

**GitHub Actions CI/CD:**  
Automated deployment workflow will be configured separately.

## 👤 Author

Mahsum - Senior Blockchain Developer

## 📄 License

This project is for interview case study purposes.
```

### 9.5 Create .gitignore

**File: `.gitignore`**
```gitignore
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
/public/build
```

### 9.6 Git Setup & Push

```bash
# Initialize git
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial commit: Football League Simulation"

# Create GitHub repository (via GitHub web interface)

# Add remote
git remote add origin https://github.com/YOUR_USERNAME/football-league-simulation.git

# Push to GitHub
git branch -M main
git push -u origin main
```

---

## 🎯 DEPLOYMENT SETUP

### Server Requirements

Your server should have:
- PHP 8.1 or higher
- Composer
- Node.js 18+
- MySQL 8.0+
- Nginx or Apache
- Git

### GitHub Actions Deployment (Preparation)

The project is structured to be deployed via GitHub Actions to your own server. 

**Prerequisites for CI/CD:**
- GitHub repository created and pushed
- SSH access to your server configured
- Web server (Nginx/Apache) configured for Laravel
- Database created on your server
- Environment variables set on server

**Directory structure on server:**
```
/var/www/football-league-simulation/
├── .env
├── storage/ (writable)
├── bootstrap/cache/ (writable)
└── ... (Laravel files)
```

**Note:** GitHub Actions workflow will be configured separately. The application is ready for automated deployment once CI/CD pipeline is set up.

---

## ✅ FINAL CHECKLIST

Before submitting:

- [ ] All migrations run successfully
- [ ] Seeders populate teams and fixtures
- [ ] "Play Next Week" button works
- [ ] "Play All" button simulates entire season
- [ ] League table updates correctly
- [ ] Championship predictions show after week 4
- [ ] Reset functionality works
- [ ] Match results display properly
- [ ] Unit tests pass
- [ ] README is comprehensive
- [ ] Code is clean and well-commented
- [ ] GitHub repository is public
- [ ] Commit history is clean
- [ ] Server environment is prepared
- [ ] Application deployed and accessible
- [ ] .env configured correctly on server
- [ ] File permissions set properly (storage, bootstrap/cache)

---

## 🎓 KEY LEARNING POINTS

This project demonstrates:

1. **OOP & SOLID Principles**
   - Service layer architecture
   - Single Responsibility Principle
   - Dependency Injection

2. **Algorithm Design**
   - Probability-based match simulation
   - Predictive analytics for championship probabilities
   - Realistic score generation

3. **Full-Stack Development**
   - Laravel API backend
   - Vue.js reactive frontend
   - RESTful API design

4. **Modern Development Practices**
   - Database migrations & seeders
   - Unit testing
   - Version control with Git
   - Clean code documentation

---

## 🚀 NEXT STEPS

1. Create the project following this guide step by step
2. Test locally thoroughly
3. Commit and push to GitHub repository
4. Test all features work correctly:
   - Play Next Week
   - Play All
   - Reset League
   - Predictions (after week 4)
5. Prepare server environment (PHP, MySQL, web server)
6. Configure GitHub Actions workflow (to be done separately)
7. Deploy to your server
8. Share GitHub repo + live demo link in email response

**Email Response Format:**
```
Subject: Football League Simulation - Case Study Completed

Hi Yağmur,

Projeyi tamamladım. İşte detaylar:

GitHub Repository: https://github.com/YOUR_USERNAME/football-league-simulation
Live Demo: https://your-domain.com

Proje özellikleri:
✅ Laravel 10 + Vue.js 3
✅ OOP service layer architecture
✅ Realistic match simulation algorithm
✅ Championship prediction engine
✅ Unit tests included
✅ Clean code & documentation

Kurulum ve kullanım talimatları README'de detaylı şekilde açıklanmış.

İyi günler,
Mahsum
```

**Good luck with your case study! 🎯**
