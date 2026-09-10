<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichit le catalogue : métadonnées bibliographiques complètes et liens
 * normalisés (catégorie, éditeur, emplacement). Les colonnes texte historiques
 * (`auteur`, `categorie`, `editeur`) sont conservées pour compatibilité et
 * servent d'affichage rapide / repli.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            if (! Schema::hasColumn('livres', 'sous_titre')) {
                $table->string('sous_titre')->nullable()->after('titre');
            }
            if (! Schema::hasColumn('livres', 'description')) {
                $table->text('description')->nullable()->after('resume');
            }
            if (! Schema::hasColumn('livres', 'mots_cles')) {
                $table->string('mots_cles')->nullable()->after('description');
            }
            if (! Schema::hasColumn('livres', 'edition')) {
                $table->string('edition', 50)->nullable()->after('annee_publication');
            }
            if (! Schema::hasColumn('livres', 'type_document')) {
                // livre|memoire|these|revue|article|rapport|manuel|numerique|autre
                $table->string('type_document', 30)->default('livre')->after('categorie');
            }
            if (! Schema::hasColumn('livres', 'niveau_academique')) {
                $table->string('niveau_academique', 50)->nullable()->after('type_document');
            }
            if (! Schema::hasColumn('livres', 'domaine')) {
                $table->string('domaine')->nullable()->after('niveau_academique');
            }
            if (! Schema::hasColumn('livres', 'categorie_id')) {
                $table->foreignId('categorie_id')->nullable()->after('categorie')
                    ->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('livres', 'editeur_id')) {
                $table->foreignId('editeur_id')->nullable()->after('editeur')
                    ->constrained('editeurs')->nullOnDelete();
            }
            if (! Schema::hasColumn('livres', 'emplacement_id')) {
                $table->foreignId('emplacement_id')->nullable()->after('emplacement_rayon')
                    ->constrained('emplacements')->nullOnDelete();
            }
        });

        Schema::table('livres', function (Blueprint $table) {
            $table->index('titre', 'livres_titre_index');
            $table->index('statut', 'livres_statut_index');
            $table->index('type_document', 'livres_type_document_index');
            $table->index('annee_publication', 'livres_annee_index');
        });
    }

    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropIndex('livres_titre_index');
            $table->dropIndex('livres_statut_index');
            $table->dropIndex('livres_type_document_index');
            $table->dropIndex('livres_annee_index');
        });

        Schema::table('livres', function (Blueprint $table) {
            foreach (['categorie_id', 'editeur_id', 'emplacement_id'] as $fk) {
                if (Schema::hasColumn('livres', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            $table->dropColumn(array_values(array_filter(
                ['sous_titre', 'description', 'mots_cles', 'edition', 'type_document', 'niveau_academique', 'domaine'],
                fn ($c) => Schema::hasColumn('livres', $c)
            )));
        });
    }
};
