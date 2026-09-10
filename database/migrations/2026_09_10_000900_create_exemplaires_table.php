<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exemplaires physiques : un livre (notice) possède N exemplaires,
 * chacun avec son code-barres, son état et son statut propres.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exemplaires')) {
            return;
        }

        Schema::create('exemplaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livre_id')->constrained('livres')->cascadeOnDelete();
            $table->string('code_barre', 50)->unique();
            $table->string('numero_inventaire', 50)->nullable()->unique();
            // neuf | bon | moyen | mauvais
            $table->string('etat', 20)->default('bon');
            // disponible | emprunte | reserve | perdu | endommage | en_reparation | retire
            $table->string('statut', 20)->default('disponible');
            $table->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->date('date_acquisition')->nullable();
            $table->decimal('prix_achat', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index(['livre_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exemplaires');
    }
};
