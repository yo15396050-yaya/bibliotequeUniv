<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Étend la table users : rôle enseignant, statut du compte, rattachement
 * académique, photo, et index de recherche rapide.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // `role` était un enum (admin|bibliothecaire|etudiant) : on passe en
            // chaîne pour accueillir `enseignant` et de futurs rôles.
            $table->string('role', 50)->default('etudiant')->change();

            if (! Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('prenom');
            }
            if (! Schema::hasColumn('users', 'statut')) {
                // actif | suspendu | diplome | radie
                $table->string('statut', 20)->default('actif')->after('actif');
            }
            if (! Schema::hasColumn('users', 'faculte')) {
                $table->string('faculte')->nullable()->after('filiere');
            }
            if (! Schema::hasColumn('users', 'departement')) {
                $table->string('departement')->nullable()->after('faculte');
            }
            if (! Schema::hasColumn('users', 'grade')) {
                $table->string('grade')->nullable()->after('departement'); // enseignants
            }
            if (! Schema::hasColumn('users', 'annee_academique_id')) {
                $table->foreignId('annee_academique_id')->nullable()
                    ->after('niveau')->constrained('annees_academiques')->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('statut', 'users_statut_index');
            $table->index('name', 'users_name_index');
        });

        DB::table('users')->whereNull('statut')->update(['statut' => 'actif']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_statut_index');
            $table->dropIndex('users_name_index');
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'annee_academique_id')) {
                $table->dropConstrainedForeignId('annee_academique_id');
            }
            $table->dropColumn(array_values(array_filter(
                ['prenom', 'photo', 'statut', 'faculte', 'departement', 'grade'],
                fn ($c) => Schema::hasColumn('users', $c)
            )));
        });
    }
};
