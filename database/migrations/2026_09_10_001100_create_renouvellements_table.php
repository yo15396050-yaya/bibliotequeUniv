<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('renouvellements')) {
            return;
        }

        Schema::create('renouvellements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprunt_id')->constrained('emprunts')->cascadeOnDelete();
            $table->foreignId('demande_par')->constrained('users')->cascadeOnDelete();
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->date('ancienne_echeance');
            $table->date('nouvelle_echeance')->nullable();
            $table->string('statut', 20)->default('en_attente'); // en_attente|accepte|refuse
            $table->string('motif_refus')->nullable();
            $table->timestamps();

            $table->index(['emprunt_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renouvellements');
    }
};
