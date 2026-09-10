<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Ajoute la hiérarchie (sous-catégories) et le slug aux catégories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('nom');
            }
            if (! Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('description')
                    ->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('categories', 'couleur')) {
                $table->string('couleur', 20)->nullable()->after('parent_id');
            }
        });

        foreach (DB::table('categories')->whereNull('slug')->get() as $categorie) {
            DB::table('categories')->where('id', $categorie->id)
                ->update(['slug' => Str::slug($categorie->nom).'-'.$categorie->id]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            $table->dropColumn(array_values(array_filter(
                ['slug', 'couleur'],
                fn ($c) => Schema::hasColumn('categories', $c)
            )));
        });
    }
};
