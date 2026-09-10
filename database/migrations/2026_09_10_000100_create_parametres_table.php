<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table centrale des règles métier configurables (durées d'emprunt, quotas,
 * montants de pénalité, délais de réservation...).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parametres')) {
            return;
        }

        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur')->nullable();
            $table->string('type', 20)->default('string'); // string|integer|decimal|boolean|json
            $table->string('groupe', 50)->default('general');
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('groupe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
