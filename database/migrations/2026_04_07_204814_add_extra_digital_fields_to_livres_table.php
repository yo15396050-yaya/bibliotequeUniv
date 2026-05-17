<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->string('extension_numerique')->nullable()->after('disponible_numerique');
            $table->boolean('autoriser_telechargement')->default(true)->after('extension_numerique');
            $table->bigInteger('taille_fichier')->nullable()->after('autoriser_telechargement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropColumn(['extension_numerique', 'autoriser_telechargement', 'taille_fichier']);
        });
    }
};
