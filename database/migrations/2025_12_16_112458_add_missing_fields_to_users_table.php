<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'bibliothecaire', 'etudiant'])->default('etudiant');
            }
            if (!Schema::hasColumn('users', 'matricule')) {
                $table->string('matricule')->unique()->nullable();
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable();
            }
            if (!Schema::hasColumn('users', 'adresse')) {
                $table->string('adresse')->nullable();
            }
            if (!Schema::hasColumn('users', 'date_naissance')) {
                $table->date('date_naissance')->nullable();
            }
            if (!Schema::hasColumn('users', 'filiere')) {
                $table->string('filiere')->nullable();
            }
            if (!Schema::hasColumn('users', 'nombre_emprunts')) {
                $table->integer('nombre_emprunts')->default(0);
            }
            if (!Schema::hasColumn('users', 'actif')) {
                $table->boolean('actif')->default(true);
            }
        });
    }

    public function down()
    {
        // On ne supprime pas les colonnes dans la méthode down pour éviter de perdre des données
        // Si vous voulez vraiment les supprimer, vous pouvez le faire manuellement
    }
}