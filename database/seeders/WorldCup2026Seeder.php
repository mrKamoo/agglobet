<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\FootballDataService;
use Illuminate\Database\Seeder;

class WorldCup2026Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer ou récupérer la saison de la Coupe du Monde
        $season = Season::updateOrCreate(
            ['competition_id' => 2000],
            [
                'name'       => 'Coupe du Monde FIFA 2026',
                'start_date' => '2026-06-11',
                'end_date'   => '2026-07-19',
                'is_active'  => true, // Active par défaut
                'type'       => 'tournament',
            ]
        );

        $this->command->info("Saison Coupe du Monde FIFA 2026 initialisée.");

        // Désactiver toutes les autres saisons pour le mode exclusif
        Season::where('id', '!=', $season->id)->update(['is_active' => false]);

        // Nettoyer les anciens matchs de cette saison pour éviter les doublons
        $season->games()->delete();
        $this->command->info("Anciens matchs de la Coupe du Monde nettoyés.");

        // 2. Lancer la synchronisation complète depuis l'API Football-Data
        $this->command->info("Démarrage de la synchronisation depuis l'API...");
        
        $service = resolve(FootballDataService::class);
        
        // Synchroniser les équipes d'abord
        $this->command->info("Synchronisation des équipes...");
        $teamStats = $service->syncTeams($season);
        $this->command->info("{$teamStats['total']} équipes synchronisées (Créées: {$teamStats['created']}, Mises à jour: {$teamStats['updated']}).");

        // Synchroniser les matchs
        $this->command->info("Synchronisation des matchs...");
        $matchStats = $service->syncMatches($season);
        $this->command->info("{$matchStats['total']} matchs synchronisés (Créés: {$matchStats['created']}, Mis à jour: {$matchStats['updated']}, Ignorés: {$matchStats['skipped']}).");
    }
}
