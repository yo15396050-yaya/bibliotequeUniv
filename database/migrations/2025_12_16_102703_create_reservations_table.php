<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('livre_id')->constrained()->onDelete('cascade');
            $table->date('date_reservation');
            $table->date('date_expiration');
            $table->enum('statut', ['active', 'expirée', 'annulée', 'honorée'])->default('active');
            $table->integer('position_file_attente')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}