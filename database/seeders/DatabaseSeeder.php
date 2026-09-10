<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Jeu de données complet de la bibliothèque universitaire.
 *
 *   php artisan migrate:fresh --seed
 *
 * Comptes de démonstration (mot de passe : password)
 *   admin@bibliotheque.univ       — Administrateur
 *   biblio@bibliotheque.univ      — Bibliothécaire
 *   enseignant@bibliotheque.univ  — Enseignant
 *   etudiant@bibliotheque.univ    — Étudiant
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,          // rôles + permissions
            ParametreSeeder::class,     // règles métier configurables
            ReferentielSeeder::class,   // années, catégories, rayonnages
            UtilisateurSeeder::class,   // comptes
            CatalogueSeeder::class,     // notices + exemplaires
            CirculationSeeder::class,   // emprunts, pénalités, réservations
        ]);

        $this->command?->newLine();
        $this->command?->info('Base de données peuplée avec succès.');
        $this->command?->table(
            ['Compte', 'Email', 'Mot de passe'],
            [
                ['Administrateur', 'admin@bibliotheque.univ', 'password'],
                ['Bibliothécaire', 'biblio@bibliotheque.univ', 'password'],
                ['Enseignant', 'enseignant@bibliotheque.univ', 'password'],
                ['Étudiant', 'etudiant@bibliotheque.univ', 'password'],
            ]
        );
    }
}
