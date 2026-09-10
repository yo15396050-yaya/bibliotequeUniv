<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La table `book_user_favorites` avait été créée sans aucune colonne
 * fonctionnelle : on la complète (pivot utilisateur ↔ livre).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('book_user_favorites')) {
            Schema::create('book_user_favorites', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        Schema::table('book_user_favorites', function (Blueprint $table) {
            if (! Schema::hasColumn('book_user_favorites', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('book_user_favorites', 'livre_id')) {
                $table->foreignId('livre_id')->constrained('livres')->cascadeOnDelete();
            }
        });

        Schema::table('book_user_favorites', function (Blueprint $table) {
            $table->unique(['user_id', 'livre_id'], 'favoris_user_livre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('book_user_favorites', function (Blueprint $table) {
            $table->dropUnique('favoris_user_livre_unique');
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('livre_id');
        });
    }
};
