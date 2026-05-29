<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            // Type de compétition : championnat ou tournoi
            $table->enum('type', ['league', 'tournament'])->default('league')->after('is_active');
            // ID de la compétition sur Football-Data.org (ex: 2000 = CdM, 2015 = Ligue 1)
            $table->unsignedInteger('competition_id')->nullable()->after('type');
        });

        Schema::table('games', function (Blueprint $table) {
            // Phase du tournoi : "Phase de groupes", "16es de finale", "Finale", etc.
            $table->string('round')->nullable()->after('matchday');
            // Groupe A-L pour la phase de groupes (null pour les phases éliminatoires)
            $table->string('group')->nullable()->after('round');
            // Stade et ville du match
            $table->string('stadium')->nullable()->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn(['type', 'competition_id']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['round', 'group', 'stadium']);
        });
    }
};
