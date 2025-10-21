# Installation de l'API Football-Data.org

## Ce qui a été créé

L'intégration avec l'API Football-Data.org a été préparée. Voici ce qui a été ajouté :

### Fichiers créés :
1. **Service API** : `app/Services/FootballDataService.php`
   - Gère la communication avec l'API Football-Data.org
   - Synchronise les matchs et les équipes

2. **Commande Artisan** : `app/Console/Commands/SyncFootballMatches.php`
   - Permet de synchroniser les matchs en ligne de commande
   - Usage : `php artisan football:sync-matches`

3. **Contrôleur Admin** : `app/Http/Controllers/Admin/FootballSyncController.php`
   - Interface d'administration pour la synchronisation

4. **Vue Admin** : `resources/views/admin/sync/index.blade.php`
   - Interface graphique pour synchroniser depuis le navigateur

---

## Étapes pour terminer l'installation

### 1. Obtenir une clé API gratuite

1. Allez sur https://www.football-data.org/
2. Cliquez sur "Register" pour créer un compte gratuit
3. Vérifiez votre email
4. Connectez-vous et accédez à votre profil
5. Copiez votre **API Token** (clé API)

**Limites du plan gratuit :**
- 10 requêtes par minute
- Toutes les compétitions disponibles (incluant la Ligue 1)

---

### 2. Configurer la clé API

#### a) Ajouter la clé dans le fichier `.env`

Ouvrez le fichier `.env` à la racine du projet et ajoutez cette ligne :

```env
FOOTBALL_DATA_API_KEY=votre_clé_api_ici
```

Remplacez `votre_clé_api_ici` par votre vraie clé API.

#### b) Ajouter la configuration dans `config/services.php`

Ouvrez le fichier `config/services.php` et ajoutez cette configuration **avant la dernière ligne `];`** :

```php
'football_data' => [
    'api_key' => env('FOOTBALL_DATA_API_KEY'),
],
```

Le fichier devrait ressembler à ça :

```php
<?php

return [
    // ... autres services ...

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'football_data' => [
        'api_key' => env('FOOTBALL_DATA_API_KEY'),
    ],

];
```

---

### 3. Ajouter les routes

Ouvrez le fichier `routes/web.php` et ajoutez ces routes dans la section **Admin routes** (après les routes Results, avant la ligne `});`) :

```php
// Football Sync
Route::get('/sync', [\App\Http\Controllers\Admin\FootballSyncController::class, 'index'])->name('sync.index');
Route::post('/sync/matches', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncMatches'])->name('sync.matches');
Route::post('/sync/teams', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncTeams'])->name('sync.teams');
```

La section admin devrait ressembler à ça :

```php
// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Seasons
    Route::resource('seasons', \App\Http\Controllers\Admin\SeasonController::class)->except(['show']);

    // Games
    Route::resource('games', \App\Http\Controllers\Admin\GameManagementController::class)->except(['show']);

    // Results
    Route::get('/results', [\App\Http\Controllers\Admin\ResultController::class, 'index'])->name('results.index');
    Route::patch('/results/{game}', [\App\Http\Controllers\Admin\ResultController::class, 'update'])->name('results.update');

    // Football Sync
    Route::get('/sync', [\App\Http\Controllers\Admin\FootballSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/matches', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncMatches'])->name('sync.matches');
    Route::post('/sync/teams', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncTeams'])->name('sync.teams');
});
```

---

### 4. Ajouter le lien dans le menu admin (Optionnel)

Pour ajouter un lien vers la page de synchronisation dans votre menu admin, trouvez le fichier layout admin (probablement `resources/views/layouts/admin.blade.php` ou `resources/views/components/admin-layout.blade.php`) et ajoutez :

```html
<a href="{{ route('admin.sync.index') }}" class="...">
    Synchronisation API
</a>
```

---

## Utilisation

### Méthode 1 : Interface Web (Recommandé)

1. Connectez-vous en tant qu'administrateur
2. Accédez à `/admin/sync` dans votre navigateur
3. Cliquez sur "Synchroniser les Équipes" (première fois uniquement)
4. Cliquez sur "Synchroniser les Matchs"

### Méthode 2 : Ligne de commande

```bash
# Synchroniser uniquement les matchs
php artisan football:sync-matches

# Synchroniser les matchs ET les équipes
php artisan football:sync-matches --teams

# Synchroniser pour une saison spécifique (par ID)
php artisan football:sync-matches --season=1
```

---

## Fonctionnalités

### Ce que fait la synchronisation :

**Synchronisation des équipes :**
- ✅ Importe toutes les équipes de Ligue 1
- ✅ Met à jour les noms, logos, stades
- ✅ Ne crée pas de doublons

**Synchronisation des matchs :**
- ✅ Importe tous les matchs de la saison
- ✅ Met à jour les scores des matchs terminés
- ✅ Crée les équipes manquantes automatiquement
- ✅ Ne crée pas de doublons de matchs
- ✅ Marque automatiquement les matchs comme terminés

---

## Planification automatique (Optionnel)

Pour synchroniser automatiquement les matchs tous les jours, ajoutez cette ligne dans `app/Console/Kernel.php` :

```php
protected function schedule(Schedule $schedule)
{
    // Synchroniser les matchs tous les jours à 6h du matin
    $schedule->command('football:sync-matches')->dailyAt('06:00');
}
```

Puis configurez le cron sur votre serveur :

```bash
* * * * * cd /chemin/vers/agglobet && php artisan schedule:run >> /dev/null 2>&1
```

---

## Dépannage

### Erreur : "Aucune saison active trouvée"
- Assurez-vous d'avoir créé une saison et de l'avoir marquée comme active dans `/admin/seasons`

### Erreur : "Erreur lors de la récupération des matchs"
- Vérifiez que votre clé API est correctement configurée dans `.env`
- Vérifiez que vous n'avez pas dépassé la limite de 10 requêtes/minute
- Testez votre clé sur https://www.football-data.org/documentation/quickstart

### Les équipes ne correspondent pas
- L'API Football-Data utilise les noms officiels des équipes
- Le service essaie de faire correspondre par nom ou nom court
- Les équipes manquantes sont créées automatiquement

---

## Documentation de l'API

Documentation officielle : https://www.football-data.org/documentation/quickstart

**ID de la Ligue 1 :** `2015`

**Endpoints utilisés :**
- `/v4/competitions/2015/matches` - Liste des matchs
- `/v4/competitions/2015/teams` - Liste des équipes

---

## Support

En cas de problème :
1. Vérifiez les logs Laravel : `storage/logs/laravel.log`
2. Testez la commande en ligne de commande pour voir les détails
3. Vérifiez votre quota API sur football-data.org

---

Bon pronostic ! ⚽
