<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal d'audit : trace toutes les opérations sensibles.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('journaux_activite')) {
            return;
        }

        Schema::create('journaux_activite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);            // creation|modification|suppression|emprunt|retour...
            $table->string('module', 50)->default('general');
            $table->string('sujet_type')->nullable(); // classe du modèle concerné
            $table->unsignedBigInteger('sujet_id')->nullable();
            $table->string('description');
            $table->json('donnees')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['sujet_type', 'sujet_id']);
            $table->index('action');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux_activite');
    }
};
