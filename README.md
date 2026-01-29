# Football League Simulation

A full-stack web application that simulates a 4-team football league with realistic match outcomes, league standings, and championship probability predictions. Built with Laravel and Vue.js, this application demonstrates modern web development practices including service layer architecture, RESTful APIs, and single-page application (SPA) design.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Docker Installation (Recommended)](#docker-installation-recommended)
- [Manual Installation](#manual-installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Architecture](#architecture)
- [Algorithm Details](#algorithm-details)
- [Development](#development)
- [Project Structure](#project-structure)
- [License](#license)

## Features

- **Round-Robin Fixture Generation**: Automatically generates fixtures for 4 teams where each team plays every other team home and away (12 total matches)

- **Realistic Match Simulation**: Simulates match outcomes based on team power ratings with home advantage (+5 power boost) and probabilistic scoring

- **Week-by-Week Progression**: Simulate matches one week at a time or play all remaining matches at once

- **Premier League Scoring**: Standard 3-1-0 point system (Win: 3pts, Draw: 1pt, Loss: 0pts)

- **Comprehensive League Table**: Displays position, played, won, drawn, lost, goals for, goals against, goal difference, and points. Standings are ordered by points, then goal difference, then goals for

- **Championship Predictions**: Calculates and displays championship probability predictions for each team starting from week 4, based on current standings and remaining fixtures

- **Match Editing**: Edit match results with automatic standings recalculation

- **Multi-Page Interface**: Clean SPA with three main pages:
- Generate Fixtures: View teams and generate league fixtures
- Fixtures: View all fixtures grouped by week
- Simulation: Simulate matches, view results, and predictions

- **Responsive Design**: Fully responsive interface with dark mode support

- **RESTful API**: Well-structured API endpoints with standardized response format

## Tech Stack

### Backend

- **Laravel 12**: PHP framework for web applications
- **PHP 8.2+**: Modern PHP with type hints and attributes
- **PostgreSQL 16**: Relational database
- **OpenAPI/Swagger**: API documentation with L5-Swagger
- **Service Layer Architecture**: SOLID principles with dedicated service classes

### Frontend

- **Vue.js 3**: Progressive JavaScript framework with Composition API
- **Inertia.js**: Modern monolith approach - SPA without API complexity
- **TypeScript**: Type-safe JavaScript
- **Tailwind CSS 4**: Utility-first CSS framework
- **Reka UI**: Component library for Vue.js

### Development Tools

- **Docker & Docker Compose**: Containerized development environment
- **Vite**: Next-generation frontend build tool
- **PHPUnit**: PHP testing framework
- **Laravel Pint**: Code formatter for PHP
- **ESLint & Prettier**: Code linting and formatting for JavaScript/TypeScript

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2 or higher** with extensions: pdo_pgsql, mbstring, exif, pcntl, bcmath, gd, zip
- **Composer**: PHP dependency manager
- **Node.js 18+** and npm: JavaScript runtime and package manager
- **PostgreSQL 16+** (or use Docker)
- **Docker & Docker Compose** (optional, but recommended for easy setup)

## Installation

### Docker Installation (Recommended)

The easiest way to get started is using Docker. This will set up all required services automatically.

1. **Clone the repository**

```bash
git clone https://github.com/mahsumurebe/football-league-simulation
cd football-league-simulation
```

2. **Copy environment file**

```bash
cp .env.example .env
```

3. **Configure environment variables** (optional)

Edit `.env` file if you need to customize database credentials or other settings. Default values work fine for local development:

```env
DB_DATABASE=football_league
DB_USERNAME=football_user
DB_PASSWORD=football_password
APP_PORT=80
```

4. **Start Docker containers**

```bash
docker-compose up -d
```

This will start three services:
- `app`: PHP-FPM application container
- `nginx`: Web server
- `postgres`: PostgreSQL database

5. **Wait for initialization**

The entrypoint script will automatically:
- Wait for PostgreSQL to be ready
- Run database migrations
- Optionally seed the database (if `SEED_DATABASE=true` in `.env`)
- Set up file permissions
- Optimize for production (if `APP_ENV=production`)

6. **Access the application**

Open your browser and navigate to:

```
http://localhost
```

If you changed `APP_PORT` in `.env`, use `http://localhost:<APP_PORT>` instead.

### Manual Installation

If you prefer to run the application without Docker, follow these steps:

1. **Clone the repository**

```bash
git clone https://github.com/mahsumurebe/football-league-simulation
cd football-league-simulation
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install Node.js dependencies**

```bash
npm install
```

4. **Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database**

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=football_league
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Create database**

```bash
createdb football_league
```

Or using PostgreSQL client:

```sql
CREATE DATABASE football_league;
```

7. **Run migrations and seeders**

```bash
php artisan migrate
php artisan db:seed
```

This will create the necessary tables and seed 4 teams (Chelsea, Arsenal, Manchester City, Liverpool) with their power ratings.

8. **Build frontend assets**

For production:

```bash
npm run build
```

For development (with hot reload):

```bash
npm run dev
```

9. **Start development server**

```bash
php artisan serve
```

10. **Access the application**

Open your browser and navigate to:

```
http://localhost:8000
```

## Configuration

### Environment Variables

Key environment variables you may want to configure:

- `APP_ENV`: Application environment (`local`, `production`)
- `APP_DEBUG`: Enable/disable debug mode (`true`, `false`)
- `APP_URL`: Application URL
- `DB_*`: Database connection settings
- `SEED_DATABASE`: Set to `true` to automatically seed database on Docker startup

### File Permissions

Ensure these directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

If using Docker, permissions are set automatically by the entrypoint script.

## Usage

### Getting Started

1. **View Teams**: Navigate to the Generate Fixtures page to see all available teams with their power ratings

2. **Generate Fixtures**: Click "Generate Fixtures" to create the round-robin schedule. This will automatically redirect you to the Fixtures page

3. **View Fixtures**: On the Fixtures page, you'll see all matches organized by week in a card layout

4. **Start Simulation**: Click "Start Simulation" to navigate to the Simulation page

5. **Simulate Matches**:
- Use "Play Next Week" to simulate one week at a time
- Use "Play All Weeks" to simulate all remaining matches at once
- Watch the league table update in real-time

6. **View Predictions**: After week 4, championship probability predictions will appear, showing each team's chance of winning the league

7. **Edit Matches**: You can edit match results through the API (see API Documentation)

8. **Reset League**: Use "Reset Data" to clear all match results and return to the initial state

### User Flow

The application follows a simple three-page flow:

1. **Generate Fixtures** (`/generate-fixtures`): View teams and generate fixtures
2. **Fixtures** (`/fixtures`): Review the generated schedule
3. **Simulation** (`/simulation`): Run simulations and view results

After resetting, you'll be redirected back to the Generate Fixtures page to start over.

## API Documentation

The application provides a RESTful API with standardized response format. All API endpoints are prefixed with `/api/league`.

### Standard Response Format

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "error": "ERROR_CODE"
}
```

**Validation Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Endpoints

#### Generate Fixtures

**POST** `/api/league/generate-fixtures`

Generates round-robin fixtures for all existing teams. Requires at least 2 teams.

**Response:**
```json
{
  "success": true,
  "data": {
    "fixtures_count": 12,
    "weeks": 6,
    "team_count": 4
  },
  "message": "Fixtures generated successfully"
}
```

#### Get Teams

**GET** `/api/league/teams`

Retrieves all teams with their power ratings.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Chelsea",
      "power": 90
    }
  ]
}
```

#### Get Matches

**GET** `/api/league/matches`

Retrieves all matches grouped by week.

**Response:**
```json
{
  "success": true,
  "data": {
    "weeks": [
      {
        "week": 1,
        "matches": [
          {
            "id": 1,
            "home_team": "Chelsea",
            "away_team": "Arsenal",
            "home_score": 2,
            "away_score": 1,
            "played": true,
            "week": 1
          }
        ]
      }
    ]
  }
}
```

#### Get Current Week

**GET** `/api/league/current-week`

Retrieves information about the current week status.

**Response:**
```json
{
  "success": true,
  "data": {
    "last_played_week": 3,
    "next_week": 4,
    "total_weeks": 6
  }
}
```

#### Get League Table

**GET** `/api/league/table`

Retrieves the current league standings, ordered by points, goal difference, and goals for.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "team_id": 1,
      "played": 4,
      "won": 3,
      "drawn": 1,
      "lost": 0,
      "goals_for": 9,
      "goals_against": 5,
      "goal_difference": 4,
      "points": 10,
      "team": {
        "id": 1,
        "name": "Chelsea",
        "power": 90
      }
    }
  ]
}
```

#### Simulate Next Week

**POST** `/api/league/simulate-week`

Simulates all matches for the next unplayed week.

**Response:**
```json
{
  "success": true,
  "data": {
    "week": 4,
    "results": [
      {
        "match_id": 7,
        "home_team": "Chelsea",
        "away_team": "Liverpool",
        "score": "2 - 1"
      }
    ],
    "table": [ ... ],
    "predictions": [ ... ]
  },
  "message": "Week simulated successfully"
}
```

#### Simulate All

**POST** `/api/league/simulate-all`

Simulates all remaining unplayed matches.

**Response:**
```json
{
  "success": true,
  "data": {
    "results": [ ... ],
    "table": [ ... ],
    "predictions": [ ... ]
  },
  "message": "All games simulated successfully"
}
```

#### Get Matches by Week

**GET** `/api/league/matches/week/{week}`

Retrieves all matches for a specific week.

**Parameters:**
- `week` (integer, required): Week number

**Response:**
```json
{
  "success": true,
  "data": {
    "week": 1,
    "matches": [ ... ]
  }
}
```

#### Update Match

**PUT** `/api/league/matches/{id}`

Updates a match result. Automatically recalculates standings.

**Parameters:**
- `id` (integer, required): Match ID

**Request Body:**
```json
{
  "home_score": 2,
  "away_score": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "game": { ... },
    "table": [ ... ]
  },
  "message": "Game updated successfully"
}
```

#### Reset League

**POST** `/api/league/reset`

Resets all match results and standings to initial state. Fixtures remain intact.

**Response:**
```json
{
  "success": true,
  "message": "League reset successfully"
}
```

#### Get Predictions

**GET** `/api/league/predictions`

Retrieves championship probability predictions. Only available after week 4.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "team_id": 1,
      "team_name": "Chelsea",
      "probability": 45.5
    }
  ]
}
```

If called before week 4:

```json
{
  "success": true,
  "message": "Predictions available after week 4"
}
```

### API Documentation (Swagger)

Interactive API documentation is available at:

```
http://localhost/api/documentation
```

(Replace `localhost` with your domain in production)

## Testing

The application includes comprehensive test coverage with 81 tests covering 2,483 assertions.

### Running Tests

```bash
php artisan test
```

### Test Structure

- **Feature Tests**: Test API endpoints and integration
- `LeagueControllerTest`: API endpoint tests
- `LeagueManagerTest`: League management functionality
- `FixtureGeneratorTest`: Fixture generation logic
- `GameSimulatorTest`: Match simulation logic
- `PredictionEngineTest`: Prediction calculations

- **Unit Tests**: Test individual components
- `StandingsCalculationTest`: Standings calculation and ordering

### Running Specific Test Suites

```bash
# Run only feature tests
php artisan test --testsuite=Feature

# Run only unit tests
php artisan test --testsuite=Unit

# Run a specific test file
php artisan test tests/Feature/LeagueControllerTest.php

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

## Architecture

### Backend Architecture

The backend follows a service layer architecture with clear separation of concerns:

**Models:**
- `Team`: Represents football teams with power ratings
- `Game`: Represents individual matches with scores and status
- `LeagueStanding`: Represents current league table positions
- `ChampionshipPrediction`: Stores probability predictions per team per week

**Services:**
- `FixtureGeneratorService`: Generates round-robin fixtures for teams
- `GameSimulatorService`: Simulates match outcomes based on team powers and probabilities
- `LeagueManagerService`: Manages league progression, standings updates, and match simulation
- `PredictionEngineService`: Calculates championship probabilities based on current standings and remaining fixtures

**Controllers:**
- `LeagueController`: Handles all API requests, delegates to services, returns standardized responses

**Traits:**
- `ApiResponse`: Provides standardized JSON response methods for consistent API responses

### Frontend Architecture

The frontend is built as a single-page application using Inertia.js:

**Pages:**
- `GenerateFixtures.vue`: Tournament teams view and fixture generation
- `Fixtures.vue`: All fixtures displayed by week
- `Simulation.vue`: Match simulation interface with league table, results, and predictions

**Components:**
- `Navigation.vue`: Header navigation bar
- `LeagueTable.vue`: League standings table
- `WeekResults.vue`: Match results for selected week
- `ChampionshipPredictions.vue`: Probability predictions display
- `ActionButtons.vue`: Simulation control buttons
- `FixturesView.vue`: Fixtures display component
- `GenerateFixtures.vue`: Fixture generation component

**Composables:**
- `useLeague.ts`: Centralized state management and API interactions for league data

**API Client:**
- `api.ts`: Type-safe API client with error handling and response parsing

### Database Schema

**teams**
- `id`: Primary key
- `name`: Team name
- `power`: Team power rating (affects match outcomes)
- `logo`: Optional team logo URL
- `created_at`, `updated_at`: Timestamps

**games**
- `id`: Primary key
- `home_team_id`: Foreign key to teams
- `away_team_id`: Foreign key to teams
- `home_score`: Home team score (nullable)
- `away_score`: Away team score (nullable)
- `week`: Week number
- `played`: Boolean indicating if match has been played
- `created_at`, `updated_at`: Timestamps

**league_standings**
- `id`: Primary key
- `team_id`: Foreign key to teams
- `played`: Number of matches played
- `won`: Number of wins
- `drawn`: Number of draws
- `lost`: Number of losses
- `goals_for`: Total goals scored
- `goals_against`: Total goals conceded
- `goal_difference`: Goals for minus goals against
- `points`: Total points (3 for win, 1 for draw, 0 for loss)
- `created_at`, `updated_at`: Timestamps

**championship_predictions**
- `id`: Primary key
- `team_id`: Foreign key to teams
- `week`: Week number
- `probability`: Championship probability percentage
- `created_at`, `updated_at`: Timestamps

## Algorithm Details

### Match Simulation

Matches are simulated using a probabilistic algorithm:

1. **Calculate Adjusted Powers**: Home team gets +5 power boost
2. **Determine Win Probabilities**: Based on power ratio between teams
3. **Apply Draw Chance**: 20% base chance for a draw
4. **Generate Outcome**: Random number determines win/draw/loss
5. **Generate Scores**: Realistic score ranges based on outcome:
- Home win: 1-3 goals for home, 0-2 goals for away
- Draw: 0-2 goals for both teams
- Away win: 1-3 goals for away, 0-2 goals for home

### Standings Calculation

Standings are calculated using Premier League rules:

1. **Points**: 3 for win, 1 for draw, 0 for loss
2. **Goal Difference**: Goals for minus goals against
3. **Ordering**:
- Primary: Points (descending)
- Secondary: Goal difference (descending)
- Tertiary: Goals for (descending)

When match results are updated, standings are recalculated from scratch to ensure consistency.

### Championship Predictions

Predictions are calculated starting from week 4:

1. **Check Minimum Week**: Predictions only available after week 4
2. **Calculate Remaining Games**: For each team, identify remaining unplayed matches
3. **Estimate Expected Points**: Based on team power, opponent power, and home/away status
4. **Project Final Points**: Current points + expected points from remaining games
5. **Calculate Probabilities**: Relative probability based on projected final points
6. **Normalize**: Ensure probabilities sum to 100%

If all games are played, the team with the most points gets 100% probability.

### Fixture Generation

Fixtures are generated using a round-robin algorithm:

1. **Team Pairing**: Each team plays every other team twice (home and away)
2. **Week Distribution**: Matches are distributed across weeks to ensure each team plays once per week
3. **Total Matches**: For 4 teams, this results in 12 matches (6 weeks × 2 matches per week)

## Development

### Development Commands

**Start development server:**
```bash
composer dev
```

This runs:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reload)

**Build frontend for production:**
```bash
npm run build
```

**Run code formatters:**
```bash
# PHP
composer lint

# JavaScript/TypeScript
npm run lint
npm run format
```

**Clear caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Code Style

- **PHP**: Follows PSR-12 coding standards (enforced by Laravel Pint)
- **JavaScript/TypeScript**: Follows ESLint and Prettier configurations
- **Vue Components**: Uses Composition API with `<script setup>` syntax

### Hot Reload

During development, Vite provides hot module replacement (HMR) for instant updates without page refresh. Just run `npm run dev` and `composer dev` simultaneously.

## Project Structure

```
football-league-simulation/
├── app/
│   ├── Enums/              # Enumerations (Outcome)
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   ├── Middleware/     # HTTP middleware
│   │   ├── Requests/       # Form request validation
│   │   └── Traits/         # Reusable traits (ApiResponse)
│   ├── Models/             # Eloquent models
│   ├── Providers/          # Service providers
│   └── Services/           # Business logic services
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── docker/                  # Docker configuration files
├── public/                  # Public assets and entry point
├── resources/
│   ├── js/
│   │   ├── components/     # Vue components
│   │   ├── composables/    # Vue composables
│   │   ├── lib/            # Utility libraries
│   │   ├── pages/          # Inertia pages
│   │   └── types/          # TypeScript types
│   └── views/               # Blade templates
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web routes
├── storage/                 # Storage directory (logs, cache)
├── tests/                   # Test files
├── docker-compose.yaml      # Docker Compose configuration
├── Dockerfile               # Docker image definition
└── README.md               # This file
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
