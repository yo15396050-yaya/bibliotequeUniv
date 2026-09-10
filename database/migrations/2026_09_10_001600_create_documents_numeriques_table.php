<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents numériques attachés à une notice. Stockage privé : l'accès passe
 * obligatoirement par une policy, jamais par une URL publique.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documents_numeriques')) {
            return;
        }

        Schema::create('documents_numeriques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livre_id')->constrained('livres')->cascadeOnDelete();
            $table->string('titre');
            $table->string('chemin');                  // chemin sur le disque privé
            $table->string('nom_original');
            $table->string('format', 10);              // pdf|epub|docx...
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('taille')->default(0);
            // public | authentifie | etudiant | enseignant | personnel
            $table->string('visibilite', 20)->default('authentifie');
            $table->boolean('autoriser_telechargement')->default(true);
            $table->unsignedInteger('nombre_telechargements')->default(0);
            $table->foreignId('ajoute_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('livre_id');
            $table->index('format');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_numeriques');
    }
};
