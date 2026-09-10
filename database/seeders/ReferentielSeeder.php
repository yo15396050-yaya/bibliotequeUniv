<?php

namespace Database\Seeders;

use App\Models\AnneeAcademique;
use App\Models\Bibliotheque;
use App\Models\Categorie;
use App\Models\Emplacement;
use App\Models\Rayon;
use App\Models\Salle;
use Illuminate\Database\Seeder;

/**
 * Référentiels : années académiques, catégories (avec sous-catégories)
 * et hiérarchie physique des rayonnages.
 */
class ReferentielSeeder extends Seeder
{
    /** Catégorie => sous-catégories. */
    private const CATEGORIES = [
        'Informatique' => ['Algorithmique', 'Réseaux', 'Bases de données', 'Intelligence artificielle'],
        'Mathématiques' => ['Analyse', 'Algèbre', 'Probabilités et statistiques'],
        'Droit' => ['Droit privé', 'Droit public', 'Droit des affaires'],
        'Économie' => ['Microéconomie', 'Macroéconomie', 'Économie du développement'],
        'Gestion' => ['Comptabilité', 'Marketing', 'Gestion des ressources humaines'],
        'Médecine' => ['Anatomie', 'Pharmacologie', 'Santé publique'],
        'Sciences' => ['Physique', 'Chimie', 'Biologie'],
        'Lettres' => ['Littérature française', 'Littérature africaine', 'Linguistique'],
        'Philosophie' => ['Philosophie politique', 'Épistémologie'],
        'Ingénierie' => ['Génie civil', 'Génie électrique', 'Génie mécanique'],
    ];

    public function run(): void
    {
        $anneeCourante = (int) date('n') >= 9 ? (int) date('Y') : (int) date('Y') - 1;

        foreach ([-1, 0] as $decalage) {
            $debut = $anneeCourante + $decalage;
            AnneeAcademique::updateOrCreate(['libelle' => $debut.'-'.($debut + 1)], [
                'date_debut' => $debut.'-09-01',
                'date_fin' => ($debut + 1).'-08-31',
                'active' => $decalage === 0,
            ]);
        }

        $index = 1;
        foreach (self::CATEGORIES as $nom => $sousCategories) {
            $parent = Categorie::updateOrCreate(['code_categorie' => sprintf('C%02d', $index)], [
                'nom' => $nom,
                'description' => "Ouvrages du domaine « {$nom} ».",
            ]);

            $sousIndex = 1;
            foreach ($sousCategories as $sousNom) {
                Categorie::updateOrCreate(['code_categorie' => sprintf('C%02d-%d', $index, $sousIndex)], [
                    'nom' => $sousNom,
                    'parent_id' => $parent->id,
                ]);
                $sousIndex++;
            }

            $index++;
        }

        $bibliotheque = Bibliotheque::updateOrCreate(['code' => 'BC'], [
            'nom' => 'Bibliothèque centrale',
            'adresse' => 'Campus universitaire — Bâtiment A',
            'telephone' => '+225 00 00 00 00',
            'active' => true,
        ]);

        foreach (['A' => 'Salle A — Sciences et technologies', 'B' => 'Salle B — Sciences humaines'] as $code => $nomSalle) {
            $salle = Salle::updateOrCreate(
                ['bibliotheque_id' => $bibliotheque->id, 'code' => $code],
                ['nom' => $nomSalle, 'capacite' => 120]
            );

            $domaines = $code === 'A'
                ? ['Informatique', 'Mathématiques', 'Sciences', 'Ingénierie', 'Médecine']
                : ['Droit', 'Économie', 'Gestion', 'Lettres', 'Philosophie'];

            foreach ($domaines as $position => $domaine) {
                $rayon = Rayon::updateOrCreate(
                    ['salle_id' => $salle->id, 'code' => strtoupper(substr($domaine, 0, 3))],
                    ['nom' => 'Rayon '.$domaine, 'domaine' => $domaine]
                );

                for ($etagere = 1; $etagere <= 4; $etagere++) {
                    Emplacement::updateOrCreate([
                        'rayon_id' => $rayon->id,
                        'etagere' => sprintf('%02d', $etagere),
                        'position' => 'G'.($position + 1),
                    ], [
                        'cote' => $rayon->code.'-'.sprintf('%02d', $etagere),
                    ]);
                }
            }
        }

        $this->command?->info('Référentiels créés : catégories, années académiques, rayonnages.');
    }
}
