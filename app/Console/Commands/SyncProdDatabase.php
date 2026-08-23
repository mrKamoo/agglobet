<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;

class SyncProdDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync-prod {--keep-backup : Conserver le fichier SQL local après l\'importation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise la base de données de production vers le local (PROD -> DEV) via SSH';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Vérifications initiales
        if (config('database.default') !== 'mysql') {
            $this->error('❌ Cette commande ne supporte actuellement que les connexions MySQL en local.');
            return self::FAILURE;
        }

        $sshHost = env('PROD_SSH_HOST');
        $sshPort = env('PROD_SSH_PORT', 22);
        $sshUser = env('PROD_SSH_USERNAME');
        $sshPass = env('PROD_SSH_PASSWORD');

        $prodDbHost = env('PROD_DB_HOST', '127.0.0.1');
        $prodDbPort = env('PROD_DB_PORT', 3306);
        $prodDbName = env('PROD_DB_DATABASE');
        $prodDbUser = env('PROD_DB_USERNAME');
        $prodDbPass = env('PROD_DB_PASSWORD');

        if (!$sshHost || !$sshUser || !$sshPass || !$prodDbName || !$prodDbUser || !$prodDbPass) {
            $this->error('❌ Configuration incomplète dans le fichier .env.');
            $this->warn('Veuillez renseigner toutes les variables PROD_SSH_* et PROD_DB_* dans votre fichier .env.');
            return self::FAILURE;
        }

        $this->info('🚀 Connexion au serveur de production via SSH...');
        
        try {
            // 2. Connexion SSH
            $ssh = new SSH2($sshHost, (int) $sshPort);
            if (!$ssh->login($sshUser, $sshPass)) {
                $this->error('❌ Échec de la connexion SSH (identifiants incorrects).');
                return self::FAILURE;
            }
            $this->info('✅ Connecté en SSH.');

            // 3. Exécution de mysqldump sur le serveur distant
            $remoteFilename = 'prod_dump_' . date('Y-m-d-His') . '.sql';
            $remotePath = '/tmp/' . $remoteFilename;
            
            $this->info('📦 Création de la sauvegarde sur le serveur distant...');
            
            // Échapper le mot de passe pour la commande distante
            $escapedPassword = str_replace('"', '\\"', $prodDbPass);
            $dumpCommand = sprintf(
                'mysqldump -h %s -P %s -u %s -p"%s" %s > %s',
                escapeshellarg($prodDbHost),
                escapeshellarg($prodDbPort),
                escapeshellarg($prodDbUser),
                $escapedPassword,
                escapeshellarg($prodDbName),
                escapeshellarg($remotePath)
            );

            // Exécuter et vérifier les erreurs (si le fichier n'est pas créé ou est vide)
            $ssh->exec($dumpCommand);
            
            $checkSize = trim($ssh->exec("stat -c %s $remotePath 2>/dev/null || wc -c < $remotePath 2>/dev/null"));
            
            if (empty($checkSize) || (int) $checkSize === 0) {
                $this->error('❌ La commande mysqldump a échoué sur le serveur distant ou a généré un fichier vide.');
                return self::FAILURE;
            }

            $this->info("✅ Sauvegarde distante créée ({$checkSize} octets).");

            // 4. Téléchargement via SFTP
            $this->info('📥 Téléchargement du fichier de sauvegarde...');
            
            $sftp = new SFTP($sshHost, (int) $sshPort);
            if (!$sftp->login($sshUser, $sshPass)) {
                $this->error('❌ Échec de la connexion SFTP.');
                // Nettoyage distant
                $ssh->exec("rm -f $remotePath");
                return self::FAILURE;
            }

            $localBackupDir = storage_path('app/backups');
            if (!file_exists($localBackupDir)) {
                mkdir($localBackupDir, 0755, true);
            }
            $localPath = $localBackupDir . '/' . $remoteFilename;

            if (!$sftp->get($remotePath, $localPath)) {
                $this->error('❌ Échec du téléchargement du fichier.');
                $ssh->exec("rm -f $remotePath");
                return self::FAILURE;
            }

            $this->info('✅ Fichier téléchargé localement.');

            // 5. Nettoyage du fichier distant
            $ssh->exec("rm -f $remotePath");
            $this->info('🧹 Fichier temporaire distant supprimé.');

            // 6. Importation locale
            $this->info('💾 Importation de la base de données en local...');
            
            $localDbName = config('database.connections.mysql.database');
            $localDbUser = config('database.connections.mysql.username');
            $localDbPass = config('database.connections.mysql.password');
            $localDbHost = config('database.connections.mysql.host');
            $localDbPort = config('database.connections.mysql.port', 3306);

            $localPasswordPart = $localDbPass ? '-p' . escapeshellarg($localDbPass) : '';
            
            // Sur Windows, la redirection < fonctionne également dans cmd.exe et PowerShell.
            $importCommand = sprintf(
                'mysql -h %s -P %s -u %s %s %s < %s',
                escapeshellarg($localDbHost),
                escapeshellarg($localDbPort),
                escapeshellarg($localDbUser),
                $localPasswordPart,
                escapeshellarg($localDbName),
                escapeshellarg($localPath)
            );

            $output = null;
            $resultCode = null;
            exec($importCommand, $output, $resultCode);

            if ($resultCode !== 0) {
                $this->error('❌ Erreur lors de l\'importation locale de la base de données.');
                $this->warn('Vous pouvez essayer d\'importer manuellement le fichier situé ici : ' . $localPath);
                return self::FAILURE;
            }

            $this->info('✅ Base de données locale synchronisée avec succès !');

            // 7. Nettoyage local si demandé
            if (!$this->option('keep-backup')) {
                unlink($localPath);
                $this->info('🧹 Fichier de sauvegarde local nettoyé.');
            } else {
                $this->info('💾 Fichier de sauvegarde conservé ici : ' . $localPath);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Une erreur est survenue : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
