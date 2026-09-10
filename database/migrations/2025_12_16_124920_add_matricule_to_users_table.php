<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter seulement les colonnes qui n'existent pas
            if (! Schema::hasColumn('users', 'matricule')) {
                $table->string('matricule')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable()->after('matricule');
            }
            if (! Schema::hasColumn('users', 'adresse')) {
                $table->string('adresse')->nullable()->after('telephone');
            }
            if (! Schema::hasColumn('users', 'date_naissance')) {
                $table->date('date_naissance')->nullable()->after('adresse');
            }
            if (! Schema::hasColumn('users', 'filiere')) {
                $table->string('filiere')->nullable()->after('date_naissance');
            }
            if (! Schema::hasColumn('users', 'niveau')) {
                $table->string('niveau')->nullable()->after('filiere');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('etudiant')->after('niveau');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['matricule', 'telephone', 'adresse', 'date_naissance', 'filiere', 'niveau', 'role']);
        });
    }
};
