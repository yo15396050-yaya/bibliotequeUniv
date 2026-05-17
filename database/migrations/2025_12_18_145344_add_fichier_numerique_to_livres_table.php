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
            $table->string('fichier_numerique')->nullable()->after('resume');
            $table->boolean('disponible_numerique')->default(false)->after('fichier_numerique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropColumn(['fichier_numerique', 'disponible_numerique']);
        });
    }
};
