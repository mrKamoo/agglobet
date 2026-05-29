<?php

namespace App\Console\Commands;

use App\Models\Season;
use App\Services\FootballDataService;
use Illuminate\Console\Command;

class SyncFootballMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'football:sync-matches
                            {--season= : ID de la saison (par défaut: saison active)}
                            {--teams : Synchroniser également les équipes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les matchs de la Ligue 1 depuis Football-Data.org';

    /**
     * Execute the console command.
     */
    public function handle(FootballDataService $service): int
    {
        $this->info('🚀 Démarrage de la synchronisation...');

        try {
            // Récupérer la saison
            $seasonId = $this->option('season');
            $season = $seasonId
                ? Season::findOrFail($seasonId)
                : Season::where('is_active', true)->first();

            if (!$season) {
                $this->error('❌ Aucune saison active trouvée. Veuillez créer une saison d\'abord.');
                return self::FAILURE;
            }

            $this->info("📅 Saison: {$season->name}");

            // Synchroniser les équipes si demandé
            if ($this->option('teams')) {
                $this->info('👕 Synchronisation des équipes...');
                $teamStats = $service->syncTeams($season);
                $this->info("✅ Équipes synchronisées:");
                $this->table(
                    ['Total', 'Créées', 'Mises à jour'],
                    [[$teamStats['total'], $teamStats['created'], $teamStats['updated']]]
                );
            }

            // Synchroniser les matchs
            $this->info('⚽ Synchronisation des matchs...');
            $this->newLine();

            $matchStats = $service->syncMatches($season);

            // Afficher les résultats
            $this->newLine();
            $this->info('✅ Synchronisation terminée avec succès!');
            $this->table(
                ['Total', 'Créés', 'Mis à jour', 'Ignorés'],
                [[
                    $matchStats['total'],
                    $matchStats['created'],
                    $matchStats['updated'],
                    $matchStats['skipped']
                ]]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡 Vérifiez que votre clé API est configurée dans le fichier .env');
            $this->warn('   FOOTBALL_DATA_API_KEY=votre_clé');

            return self::FAILURE;
        }
    }
}
