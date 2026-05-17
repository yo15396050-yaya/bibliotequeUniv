<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpruntsTable extends Migration
{
    public function up()
    {
        Schema::create('emprunts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('livre_id')->constrained()->onDelete('cascade');
            $table->date('date_emprunt');
            $table->date('date_retour_prevue');
            $table->date('date_retour_effective')->nullable();
            $table->enum('statut', ['en cours', 'retourné', 'en retard', 'perdu'])->default('en cours');
            $table->decimal('amende', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emprunts');
    }
}