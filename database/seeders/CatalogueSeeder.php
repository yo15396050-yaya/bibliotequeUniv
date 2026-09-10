<?php

namespace Database\Seeders;

use App\Models\Auteur;
use App\Models\Categorie;
use App\Models\Editeur;
use App\Models\Emplacement;
use App\Models\Exemplaire;
use App\Models\Livre;
use Illuminate\Database\Seeder;

/**
 * Catalogue de démonstration : 500 notices, leurs auteurs, éditeurs
 * et leurs exemplaires physiques rangés dans les rayons.
 */
class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $auteurs = Auteur::factory()->count(80)->create();
        $editeurs = Editeur::factory()->count(15)->create();

        $categories = Categorie::whereNotNull('parent_id')->get();
        $emplacements = Emplacement::with('rayon')->get();

        $this->command?->info('Création de 500 notices et de leurs exemplaires...');
        $barre = $this->command?->getOutput()->createProgressBar(500);

        Livre::factory()->count(500)->create()->each(function (Livre $livre) use (
            $auteurs, $editeurs, $categories, $emplacements, $barre
        ) {
            $categorie = $categories->where('parent.nom', $livre->categorie)->first()
                ?? $categories->random();

            $editeur = $editeurs->random();

            // On range l'ouvrage dans un rayon du bon domaine si possible.
            $emplacement = $emplacements->first(
                fn (Emplacement $e) => $e->rayon?->domaine === $livre->categorie
            ) ?? $emplacements->random();

            $livre->forceFill([
                'categorie_id' => $categorie->id,
                'editeur_id' => $editeur->id,
                'editeur' => $editeur->nom,
                'emplacement_id' => $emplacement->id,
                'emplacement_rayon' => $emplacement->cote,
            ])->save();

            $auteursDuLivre = $auteurs->random(random_int(1, 2));
            $livre->auteurs()->sync($auteursDuLivre->pluck('id'));
            $livre->forceFill([
                'auteur' => $auteursDuLivre->map->nom_complet->implode(', '),
            ])->save();

            $exemplaires = [];
            for ($i = 1; $i <= $livre->exemplaires_totaux; $i++) {
                $exemplaires[] = [
                    'livre_id' => $livre->id,
                    'code_barre' => Exemplaire::genererCodeBarre($livre).'-'.$livre->id.$i,
                    'etat' => 'bon',
                    'statut' => Exemplaire::STATUT_DISPONIBLE,
                    'emplacement_id' => $emplacement->id,
                    'date_acquisition' => now()->subDays(random_int(30, 1500))->toDateString(),
                    'prix_achat' => random_int(5000, 45000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Exemplaire::insert($exemplaires);

            $livre->synchroniserCompteurs();
            $barre?->advance();
        });

        $barre?->finish();
        $this->command?->newLine();

        // Quelques exemplaires hors circulation pour alimenter les rapports.
        Exemplaire::inRandomOrder()->limit(8)->update(['statut' => Exemplaire::STATUT_PERDU]);
        Exemplaire::where('statut', Exemplaire::STATUT_DISPONIBLE)
            ->inRandomOrder()->limit(6)->update(['statut' => Exemplaire::STATUT_ENDOMMAGE]);

        $this->command?->info('Catalogue créé : '.Livre::count().' notices, '
            .Exemplaire::count().' exemplaires.');
    }
}
