<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_admin and exclude_from_leaderboard to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->boolean('exclude_from_leaderboard')->default(false)->after('is_admin');
        });

        // Create seasons table
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: "2024/2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Create teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('city')->nullable();
            $table->string('stadium')->nullable();
            $table->timestamps();
        });

        // Create games table
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('home_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('matchday'); // Numéro de journée
            $table->dateTime('match_date');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->timestamps();
        });

        // Create predictions table
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('home_score');
            $table->integer('away_score');
            $table->integer('points_earned')->nullable();
            $table->timestamps();

            // Un utilisateur ne peut faire qu'un seul pronostic par match
            $table->unique(['user_id', 'game_id']);
        });

        // Create points_rules table
        Schema::create('points_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('exact_score'); // Points pour score exact
            $table->integer('correct_difference'); // Points pour bonne différence de buts
            $table->integer('correct_winner'); // Points pour bon vainqueur/match nul
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('games');
        Schema::dropIfExists('points_rules');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('seasons');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'exclude_from_leaderboard']);
        });
    }
};
