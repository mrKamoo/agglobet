# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Agglobet is a football prediction application built with Laravel 12 and Tailwind CSS v4. Users make predictions on Ligue 1 matches and earn points based on accuracy. The application features automated match/team synchronization with the Football-Data.org API and automatic point calculation.

## Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade templates, Vue.js 3, Alpine.js, Tailwind CSS v4, Vite
- **Database**: SQLite (development), configurable for production
- **API Integration**: Football-Data.org API (Ligue 1 competition ID: 2015)

## Development Commands

### Setup & Installation
```bash
composer setup              # Full setup: install deps, copy .env, generate key, migrate, build assets
composer install           # Install PHP dependencies
npm install               # Install Node dependencies
php artisan key:generate  # Generate application key
php artisan migrate       # Run database migrations
```

### Development Server
```bash
composer dev              # Start all services: server, queue, logs, and vite (requires concurrently)
# This runs 4 processes simultaneously:
# - php artisan serve (server on port 8000)
# - php artisan queue:listen --tries=1 (queue worker)
# - php artisan pail --timeout=0 (log viewer)
# - npm run dev (Vite dev server)

# Or run individually:
php artisan serve         # Development server (http://localhost:8000)
npm run dev              # Vite dev server for assets
php artisan queue:listen # Queue worker
```

### Building Assets
```bash
npm run build            # Build production assets with Vite
npm run dev             # Start Vite dev server with HMR
```

### Testing
```bash
composer test           # Clear config cache and run PHPUnit tests
php artisan test       # Run tests directly
```

### Code Quality
```bash
vendor/bin/pint        # Laravel Pint (code style fixer)
```

### Football API Synchronization
```bash
php artisan football:sync-matches              # Sync matches from API
php artisan football:sync-matches --teams      # Sync matches AND teams
php artisan football:sync-matches --season=1   # Sync for specific season ID
```

## Core Architecture

### Database Schema

**Users** → **Predictions** ← **Games**
- Users make predictions (home_score, away_score) on games
- Points are calculated and stored in `predictions.points_earned`

**Seasons** → **Games** ← **Teams**
- Each game belongs to a season and has home/away teams
- Games track match results (home_score, away_score, is_finished)

**PointsRules** (singleton pattern)
- One active rule at a time (`is_active = true`)
- Defines scoring system: exact_score (5), correct_difference (3), correct_winner (1)

### Key Models & Relationships

**Game** (`app/Models/Game.php`)
- `belongsTo`: season, homeTeam, awayTeam
- `hasMany`: predictions
- Fields: matchday, match_date, home_score, away_score, is_finished

**Prediction** (`app/Models/Prediction.php`)
- `belongsTo`: user, game
- Fields: home_score, away_score, points_earned

**User** (`app/Models/User.php`)
- Has `is_admin` boolean for admin access
- `hasMany`: predictions

### Points Calculation Logic

The points calculation algorithm (found in `app/Http/Controllers/Admin/ResultController.php:44-75` and `app/Services/FootballDataService.php`) follows this priority:

1. **Exact score** → 5 points (or `points_rules.exact_score`)
2. **Correct goal difference** → 3 points (or `points_rules.correct_difference`)
3. **Correct winner/draw** → 1 point (or `points_rules.correct_winner`)
4. **Incorrect** → 0 points

Points are calculated automatically when:
- Admin enters results via `/admin/results`
- Matches are synchronized from API and marked as finished
- Manual recalculation is triggered via `/admin/sync` (recalculate button)

### Route Structure

**Public Routes**:
- `/` - Home page (upcoming matches)
- `/leaderboard` - Global rankings

**Authenticated Routes** (`auth` + `verified` middleware):
- `/games` - Browse matches
- `/games/{game}` - Game details with prediction form
- `/predictions/store` - Submit/update predictions
- `/my-predictions` - User's prediction history
- `/profile` - Profile management

**Admin Routes** (`auth` + `verified` + `admin` middleware, prefix: `/admin`):
- `/admin/dashboard` - Admin dashboard
- `/admin/seasons` - Manage seasons (resource controller)
- `/admin/games` - Manage games manually (resource controller)
- `/admin/results` - Enter match results & trigger point calculation
- `/admin/sync` - Football-Data.org API synchronization interface
- `/admin/sync/matches` - POST: Sync matches
- `/admin/sync/teams` - POST: Sync teams
- `/admin/sync/recalculate-points` - POST: Recalculate all points

### Service Layer

**FootballDataService** (`app/Services/FootballDataService.php`):
- Communicates with Football-Data.org API v4
- `syncMatches()`: Fetches and syncs Ligue 1 matches, auto-calculates points for newly finished games
- `syncTeams()`: Fetches and syncs Ligue 1 teams (logos, stadium info)
- `calculatePoints(Game $game)`: Calculates points for all predictions on a specific game
- `recalculateAllPoints(?Season $season)`: Recalculates points for all finished games
- Handles rate limiting (10 requests/minute on free tier)
- Uses `FOOTBALL_DATA_API_KEY` from `.env` and `config/services.php`

## Configuration

### Environment Variables

Required in `.env`:
```env
FOOTBALL_DATA_API_KEY=your_api_key_here  # Get from football-data.org
```

Must be registered in `config/services.php`:
```php
'football_data' => [
    'api_key' => env('FOOTBALL_DATA_API_KEY'),
],
```

### Admin Access

Set `is_admin = 1` in the `users` table to grant admin privileges.

## Important Development Notes

### Prediction Rules
- Users can only predict before match start time (`match_date`)
- Predictions are locked once `match_date` has passed
- Users can modify predictions unlimited times before lockdown
- One prediction per user per game

### Point Calculation Triggers
1. **Manual entry**: Admin enters result via `/admin/results` → immediate calculation
2. **API sync**: Match status changes from not-finished to finished → automatic calculation
3. **Manual recalc**: Admin triggers recalculation for all finished games

### API Integration Details
- **API URL**: https://api.football-data.org/v4/
- **Competition**: Ligue 1 (ID: 2015)
- **Endpoints used**:
  - `/competitions/2015/matches` - Match data
  - `/competitions/2015/teams` - Team data
- **Rate limit**: 10 requests/minute (free tier)
- **Auto-matching**: Service attempts to match API teams with existing teams by name/short_name
- **Auto-creation**: Missing teams are created automatically during match sync

### Frontend Stack
- **Blade templates** in `resources/views/`
- **Vue.js 3** for dynamic components (games list, predictions)
  - Components in `resources/js/components/`
  - Composition API with `<script setup>`
  - Auto-registered globally via `app.js`
- **Alpine.js** for simple interactivity (modals, toggles)
- **Tailwind CSS v4** with `@tailwindcss/forms` plugin
- **Vite** for asset bundling with Vue plugin (`resources/css/app.css`, `resources/js/app.js`)

### Vue.js Integration
The `/games` page uses Vue.js for a dynamic, SPA-like experience:
- **GamesList.vue**: Main component with filters (matchday, status, search)
- **GameCard.vue**: Individual match display with live countdown
- **PredictionForm.vue**: AJAX prediction submission without page reload
- **API endpoint**: `GET /api/games` returns JSON with filters support
- See `VUE_INTEGRATION.md` and `GAMES_PAGE_VUE.md` for detailed documentation

### Testing Strategy
- PHPUnit configured in `phpunit.xml`
- Tests located in `tests/` directory
- Run with `composer test` or `php artisan test`

## Common Development Workflows

### Adding a new match manually
1. Ensure a season exists and is active (`/admin/seasons`)
2. Ensure teams exist (`/admin/sync` → "Synchroniser les Équipes" or create manually)
3. Create game via `/admin/games/create`
4. After match finishes, enter result via `/admin/results`

### Setting up Football API sync
1. Register at football-data.org for free API key
2. Add `FOOTBALL_DATA_API_KEY` to `.env`
3. Add configuration to `config/services.php`
4. Run `/admin/sync` → "Synchroniser les Équipes" (first time only)
5. Run `/admin/sync` → "Synchroniser les Matchs"

### Modifying point calculation rules
1. Update the active `PointsRule` record in database
2. Run point recalculation from `/admin/sync` or manually trigger `FootballDataService::recalculateAllPoints()`

### Debugging
- Laravel logs: `storage/logs/laravel.log`
- Use `php artisan pail` for live log viewing
- Use `dd()` or `dump()` for debugging in Blade/Controllers
- Check browser console for Alpine.js errors

## File Locations Reference

- **Controllers**: `app/Http/Controllers/` (Admin controllers in `Admin/` subdirectory)
- **Models**: `app/Models/`
- **Services**: `app/Services/`
- **Routes**: `routes/web.php`, `routes/auth.php`
- **Views**: `resources/views/`
- **Migrations**: `database/migrations/`
- **Commands**: `app/Console/Commands/`
- **Config**: `config/`
- **Assets**: `resources/css/`, `resources/js/`
- **Public**: `public/` (compiled assets go to `public/build/`)

## Documentation Files

Project-specific documentation is maintained in root-level markdown files (in French):
- `FOOTBALL_API_INSTALLATION.md` - API setup guide
- `COMMENT_SONT_CALCULES_LES_POINTS.md` - Points calculation explanation
- `CALCUL_AUTOMATIQUE_POINTS.md` - Automatic point calculation feature
- Additional feature-specific docs for navigation, logos, routes, etc.
