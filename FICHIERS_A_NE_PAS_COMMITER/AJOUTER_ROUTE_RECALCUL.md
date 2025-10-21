# Ajouter la route de recalcul des points

## 📍 Fichier à modifier

`routes/web.php`

---

## ✅ Route à ajouter

Dans la section **Admin routes** (où se trouvent déjà les routes de synchronisation), ajoutez cette ligne :

```php
Route::post('/sync/recalculate-points', [\App\Http\Controllers\Admin\FootballSyncController::class, 'recalculatePoints'])->name('sync.recalculate-points');
```

---

## 📝 Emplacement exact

La section admin avec les routes de synchronisation devrait ressembler à ceci après modification :

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

    // Football-Data.org Synchronization
    Route::get('/sync', [\App\Http\Controllers\Admin\FootballSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/matches', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncMatches'])->name('sync.matches');
    Route::post('/sync/teams', [\App\Http\Controllers\Admin\FootballSyncController::class, 'syncTeams'])->name('sync.teams');
    Route::post('/sync/recalculate-points', [\App\Http\Controllers\Admin\FootballSyncController::class, 'recalculatePoints'])->name('sync.recalculate-points'); // ⬅️ NOUVELLE LIGNE
});
```

---

## 🎯 Ligne à ajouter uniquement

Si vous avez déjà les autres routes de sync, ajoutez juste cette ligne après `sync.teams` :

```php
Route::post('/sync/recalculate-points', [\App\Http\Controllers\Admin\FootballSyncController::class, 'recalculatePoints'])->name('sync.recalculate-points');
```

---

## ✅ Vérification

Après avoir ajouté la route, vous pouvez vérifier qu'elle est bien enregistrée avec :

```bash
php artisan route:list --name=sync
```

Vous devriez voir :
```
admin.sync.index             | GET    | admin/sync
admin.sync.matches           | POST   | admin/sync/matches
admin.sync.teams             | POST   | admin/sync/teams
admin.sync.recalculate-points| POST   | admin/sync/recalculate-points  ⬅️ Nouvelle route
```

---

## 🔗 Route utilisée par

Cette route est appelée par le formulaire dans `resources/views/admin/sync/index.blade.php` :

```blade
<form action="{{ route('admin.sync.recalculate-points') }}" method="POST">
    @csrf
    <!-- ... -->
    <button type="submit">Recalculer les Points</button>
</form>
```

---

## 🚨 Important

**Cette route doit être ajoutée UNIQUEMENT si vous avez installé les fichiers suivants :**

1. ✅ `app/Services/FootballDataService_v2.php` → renommé en `FootballDataService.php`
2. ✅ `app/Http/Controllers/Admin/FootballSyncController_v2.php` → renommé en `FootballSyncController.php`
3. ✅ `resources/views/admin/sync/index_v2.blade.php` → renommé en `index.blade.php`

Si vous n'avez pas encore installé ces fichiers, consultez d'abord `CALCUL_AUTOMATIQUE_POINTS.md`.

---

Voilà ! Une fois cette route ajoutée, le bouton "Recalculer les Points" dans l'interface admin fonctionnera. 🎉
