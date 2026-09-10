<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auteurs')) {
            Schema::create('auteurs', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('slug')->unique();
                $table->text('biographie')->nullable();
                $table->string('nationalite')->nullable();
                $table->date('date_naissance')->nullable();
                $table->date('date_deces')->nullable();
                $table->string('photo')->nullable();
                $table->timestamps();

                $table->index('nom');
            });
        }

        if (! Schema::hasTable('editeurs')) {
            Schema::create('editeurs', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->unique();
                $table->string('slug')->unique();
                $table->string('pays')->nullable();
                $table->string('site_web')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auteur_livre')) {
            Schema::create('auteur_livre', function (Blueprint $table) {
                $table->foreignId('auteur_id')->constrained('auteurs')->cascadeOnDelete();
                $table->foreignId('livre_id')->constrained('livres')->cascadeOnDelete();
                $table->string('role', 30)->default('auteur'); // auteur|co-auteur|directeur|traducteur
                $table->primary(['auteur_id', 'livre_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auteur_livre');
        Schema::dropIfExists('editeurs');
        Schema::dropIfExists('auteurs');
    }
};
