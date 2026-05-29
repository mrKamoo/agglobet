<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add winner_team_id to seasons table
        Schema::table('seasons', function (Blueprint $table) {
            $table->foreignId('winner_team_id')->nullable()->after('competition_id')->constrained('teams')->onDelete('set null');
        });

        // 2. Create champion_predictions table
        Schema::create('champion_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // A user can predict exactly one champion team per season
            $table->unique(['user_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('champion_predictions');

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_team_id');
        });
    }
};
