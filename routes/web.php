<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/api/users/{user}/stats', [LeaderboardController::class, 'userStats'])->name('api.users.stats');

// Vue.js test route (can be removed after testing)
Route::get('/vue-test', function () {
    return view('vue-test');
})->name('vue.test');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Games
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/api/games', [GameController::class, 'getGames'])->name('api.games');

    // Predictions
    Route::post('/games/{game}/predictions', [PredictionController::class, 'store'])->name('predictions.store');
    Route::get('/my-predictions', [PredictionController::class, 'myPredictions'])->name('predictions.mine');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);

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

    Route::post('/sync/recalculate-points', [\App\Http\Controllers\Admin\FootballSyncController::class,'recalculatePoints'])->name('sync.recalculate-points');

    // Database Backup
    Route::post('/backup', [\App\Http\Controllers\Admin\DashboardController::class, 'backup'])->name('backup');
    Route::get('/backup/download/{filename}', [\App\Http\Controllers\Admin\DashboardController::class, 'downloadBackup'])->name('backup.download');
});

require __DIR__.'/auth.php';
