<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hiérarchie physique : Bibliothèque → Salle → Rayon → Emplacement (étagère).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bibliotheques')) {
            Schema::create('bibliotheques', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('code', 20)->unique();
                $table->string('adresse')->nullable();
                $table->string('telephone', 30)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('salles')) {
            Schema::create('salles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bibliotheque_id')->constrained('bibliotheques')->cascadeOnDelete();
                $table->string('nom');
                $table->string('code', 20);
                $table->integer('capacite')->nullable();
                $table->timestamps();

                $table->unique(['bibliotheque_id', 'code']);
            });
        }

        if (! Schema::hasTable('rayons')) {
            Schema::create('rayons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('salle_id')->constrained('salles')->cascadeOnDelete();
                $table->string('nom');
                $table->string('code', 20);
                $table->string('domaine')->nullable();
                $table->timestamps();

                $table->unique(['salle_id', 'code']);
            });
        }

        if (! Schema::hasTable('emplacements')) {
            Schema::create('emplacements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rayon_id')->constrained('rayons')->cascadeOnDelete();
                $table->string('etagere', 20);
                $table->string('position', 20)->nullable();
                $table->string('cote')->nullable(); // cote de rangement (Dewey, etc.)
                $table->timestamps();

                $table->unique(['rayon_id', 'etagere', 'position']);
                $table->index('cote');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('emplacements');
        Schema::dropIfExists('rayons');
        Schema::dropIfExists('salles');
        Schema::dropIfExists('bibliotheques');
    }
};
