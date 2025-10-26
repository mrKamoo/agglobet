<?php

namespace App\Console\Commands;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertMatchDatesToTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:convert-timezone {--from=UTC} {--to=Europe/Paris} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing match dates from one timezone to another';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromTimezone = $this->option('from');
        $toTimezone = $this->option('to');
        $isDryRun = $this->option('dry-run');

        $this->info("Converting match dates from {$fromTimezone} to {$toTimezone}");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        $games = Game::all();
        $count = $games->count();

        if ($count === 0) {
            $this->info('No games found in database.');
            return 0;
        }

        $this->info("Found {$count} games to convert");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $converted = 0;

        foreach ($games as $game) {
            // Parse the date from database (stored as UTC timestamp)
            // Create a Carbon instance treating the DB value as UTC
            $utcDate = Carbon::createFromFormat('Y-m-d H:i:s', $game->match_date, 'UTC');

            // Convert to target timezone - this adjusts the displayed time
            $localDate = $utcDate->setTimezone($toTimezone);

            // Get the datetime string in the new timezone (this will show local time)
            $newDateString = $localDate->format('Y-m-d H:i:s');

            if (!$isDryRun) {
                // Update directly in database to avoid Carbon auto-conversion
                DB::table('games')
                    ->where('id', $game->id)
                    ->update(['match_date' => $newDateString]);

                $converted++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($isDryRun) {
            $this->info("DRY RUN: Would convert {$count} match dates");
            $this->info("Example conversions:");
            $examples = $games->take(3);
            foreach ($examples as $example) {
                $utcDate = Carbon::createFromFormat('Y-m-d H:i:s', $example->match_date, 'UTC');
                $localDate = $utcDate->copy()->setTimezone($toTimezone);
                $this->line("  {$example->homeTeam->short_name} vs {$example->awayTeam->short_name}");
                $this->line("    Old ({$fromTimezone}): " . $utcDate->format('Y-m-d H:i:s'));
                $this->line("    New ({$toTimezone}): " . $localDate->format('Y-m-d H:i:s'));
            }
        } else {
            $this->info("Successfully converted {$converted} match dates from {$fromTimezone} to {$toTimezone}");
        }

        return 0;
    }
}
