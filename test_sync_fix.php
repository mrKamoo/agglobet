require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use App\Services\FootballDataService;
use Carbon\Carbon;

// Setup mock data
$season = Season::create([
    'name' => '2024/2025',
    'start_date' => now(),
    'end_date' => now()->addYear(),
    'is_active' => true,
]);

$team1 = Team::create(['name' => 'Team A']);
$team2 = Team::create(['name' => 'Team B']);

$oldDate = now()->subDays(5);
$newDate = now()->addDays(2);

// Create existing game with OLD date
$game = Game::create([
    'season_id' => $season->id,
    'home_team_id' => $team1->id,
    'away_team_id' => $team2->id,
    'matchday' => 1,
    'match_date' => $oldDate,
    'home_score' => null,
    'away_score' => null,
    'is_finished' => false,
]);

echo "Original Date: " . $game->match_date . "\n";

// Mock data from API with NEW date but same status/score
$matchData = [
    'id' => 12345,
    'utcDate' => $newDate->toIso8601String(),
    'status' => 'SCHEDULED',
    'matchday' => 1,
    'score' => [
        'fullTime' => ['home' => null, 'away' => null]
    ],
    'homeTeam' => ['name' => 'Team A'],
    'awayTeam' => ['name' => 'Team B'],
];

// Run sync
$service = new FootballDataService();

// Use reflection to access private method syncMatch
$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('syncMatch');
$method->setAccessible(true);

$stats = ['updated' => 0, 'created' => 0, 'skipped' => 0, 'points_calculated' => 0];
$method->invokeArgs($service, [$matchData, $season, &$stats]);

// Verify
$game->refresh();
echo "New Date: " . $game->match_date . "\n";

if ($game->match_date->eq($newDate)) {
    echo "SUCCESS: Date updated correctly.\n";
} else {
    echo "FAILURE: Date NOT updated.\n";
}
