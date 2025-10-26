<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--download : Return the backup file path for download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une sauvegarde de la base de données MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        // Créer le nom du fichier avec timestamp
        $filename = 'backup-' . $database . '-' . date('Y-m-d-His') . '.sql';

        // Créer le répertoire de backups s'il n'existe pas
        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filepath = $backupPath . '/' . $filename;

        // Construire la commande mysqldump
        $passwordPart = $password ? "-p\"{$password}\"" : '';
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > "%s"',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordPart,
            escapeshellarg($database),
            $filepath
        );

        // Exécuter la commande
        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error('Erreur lors de la création de la sauvegarde.');
            return 1;
        }

        $this->info('Sauvegarde créée avec succès : ' . $filename);

        // Si l'option --download est présente, retourner le chemin du fichier
        if ($this->option('download')) {
            $this->line($filepath);
        }

        return 0;
    }
}
