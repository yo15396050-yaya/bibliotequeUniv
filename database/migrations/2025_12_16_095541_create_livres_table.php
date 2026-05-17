<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivresTable extends Migration
{
    public function up()
    {
        Schema::create('livres', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->unique();
            $table->string('titre');
            $table->string('auteur');
            $table->string('editeur');
            $table->integer('annee_publication');
            $table->string('categorie');
            $table->string('langue')->default('Français');
            $table->integer('nombre_pages');
            $table->text('resume')->nullable();
            $table->string('emplacement_rayon');
            $table->string('image_couverture')->nullable();
            $table->integer('exemplaires_disponibles')->default(1);
            $table->integer('exemplaires_totaux')->default(1);
            $table->enum('statut', ['disponible', 'emprunté', 'réservé', 'perdu', 'en réparation'])->default('disponible');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('livres');
    }
}