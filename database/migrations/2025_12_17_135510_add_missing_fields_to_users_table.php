<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes manquantes si elles n'existent pas déjà
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'bibliothecaire', 'etudiant'])->default('etudiant')->after('password');
            }
            if (!Schema::hasColumn('users', 'matricule')) {
                $table->string('matricule')->unique()->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable()->after('matricule');
            }
            if (!Schema::hasColumn('users', 'adresse')) {
                $table->string('adresse')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('users', 'date_naissance')) {
                $table->date('date_naissance')->nullable()->after('adresse');
            }
            if (!Schema::hasColumn('users', 'filiere')) {
                $table->string('filiere')->nullable()->after('date_naissance');
            }
            if (!Schema::hasColumn('users', 'niveau')) {
                $table->string('niveau')->nullable()->after('filiere');
            }
            if (!Schema::hasColumn('users', 'nombre_emprunts')) {
                $table->integer('nombre_emprunts')->default(0)->after('niveau');
            }
            if (!Schema::hasColumn('users', 'actif')) {
                $table->boolean('actif')->default(true)->after('nombre_emprunts');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'matricule', 'telephone', 'adresse', 
                               'date_naissance', 'filiere', 'niveau', 'nombre_emprunts', 'actif']);
        });
    }
};
