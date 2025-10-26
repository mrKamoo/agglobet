<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'predictions' => Prediction::count(),
            'seasons' => Season::count(),
            'games' => Game::count(),
            'active_season' => Season::where('is_active', true)->first(),
        ];

        // Liste des sauvegardes existantes
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (file_exists($backupPath)) {
            $files = scandir($backupPath, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize($backupPath . '/' . $file),
                        'date' => filemtime($backupPath . '/' . $file),
                    ];
                }
            }
        }

        return view('admin.dashboard', compact('stats', 'backups'));
    }

    public function backup()
    {
        try {
            // Exécuter la commande de sauvegarde
            Artisan::call('db:backup', ['--download' => true]);
            $output = Artisan::output();

            // Extraire le chemin du fichier de la sortie
            $filepath = trim($output);

            if (file_exists($filepath)) {
                return response()->download($filepath)->deleteFileAfterSend(false);
            }

            return back()->with('success', 'Sauvegarde créée avec succès !');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création de la sauvegarde : ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (!file_exists($filepath)) {
            return back()->with('error', 'Fichier de sauvegarde introuvable.');
        }

        return response()->download($filepath);
    }
}
