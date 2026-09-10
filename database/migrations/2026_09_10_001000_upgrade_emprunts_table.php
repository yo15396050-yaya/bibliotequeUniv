<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rattache l'emprunt à un exemplaire précis, trace le bibliothécaire
 * responsable et les renouvellements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            if (! Schema::hasColumn('emprunts', 'exemplaire_id')) {
                $table->foreignId('exemplaire_id')->nullable()->after('livre_id')
                    ->constrained('exemplaires')->nullOnDelete();
            }
            if (! Schema::hasColumn('emprunts', 'bibliothecaire_id')) {
                $table->foreignId('bibliothecaire_id')->nullable()->after('user_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('emprunts', 'receptionne_par')) {
                $table->foreignId('receptionne_par')->nullable()->after('date_retour_effective')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('emprunts', 'nombre_renouvellements')) {
                $table->unsignedTinyInteger('nombre_renouvellements')->default(0)->after('statut');
            }
            if (! Schema::hasColumn('emprunts', 'etat_retour')) {
                $table->string('etat_retour', 20)->nullable()->after('nombre_renouvellements');
            }
        });

        Schema::table('emprunts', function (Blueprint $table) {
            $table->index('statut', 'emprunts_statut_index');
            $table->index('date_retour_prevue', 'emprunts_echeance_index');
            $table->index(['user_id', 'statut'], 'emprunts_user_statut_index');
        });
    }

    public function down(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            $table->dropIndex('emprunts_statut_index');
            $table->dropIndex('emprunts_echeance_index');
            $table->dropIndex('emprunts_user_statut_index');
        });

        Schema::table('emprunts', function (Blueprint $table) {
            foreach (['exemplaire_id', 'bibliothecaire_id', 'receptionne_par'] as $fk) {
                if (Schema::hasColumn('emprunts', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            $table->dropColumn(array_values(array_filter(
                ['nombre_renouvellements', 'etat_retour'],
                fn ($c) => Schema::hasColumn('emprunts', $c)
            )));
        });
    }
};
