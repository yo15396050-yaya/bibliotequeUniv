<?php

namespace Database\Seeders;

use App\Models\AnneeAcademique;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Comptes de démonstration : 1 administrateur, 5 bibliothécaires,
 * 12 enseignants et 100 étudiants.
 */
class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeAcademique::courante();

        User::updateOrCreate(['email' => 'admin@bibliotheque.univ'], [
            'name' => 'Administrateur Système',
            'prenom' => 'Admin',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'matricule' => 'ADMIN001',
            'telephone' => '0700000000',
            'statut' => 'actif',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        // Compte historique du projet, conservé.
        User::updateOrCreate(['email' => 'ouattarayaya@gmail.com'], [
            'name' => 'Ouattara Yaya',
            'prenom' => 'Yaya',
            'password' => 'yaya1539',
            'role' => User::ROLE_ADMIN,
            'matricule' => 'YAYA001',
            'telephone' => '0712491576',
            'statut' => 'actif',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'biblio@bibliotheque.univ'], [
            'name' => 'Bibliothécaire Principal',
            'prenom' => 'Aïcha',
            'password' => 'password',
            'role' => User::ROLE_BIBLIOTHECAIRE,
            'matricule' => 'BIBLIO001',
            'telephone' => '0700000001',
            'statut' => 'actif',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->count(4)->bibliothecaire()->create();

        User::updateOrCreate(['email' => 'enseignant@bibliotheque.univ'], [
            'name' => 'Professeur Koné',
            'prenom' => 'Ibrahim',
            'password' => 'password',
            'role' => User::ROLE_ENSEIGNANT,
            'matricule' => 'ENS0001',
            'grade' => 'Maître de conférences',
            'faculte' => 'Faculté des Sciences et Technologies',
            'departement' => 'Informatique',
            'filiere' => 'Informatique',
            'statut' => 'actif',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->count(11)->enseignant()->create(['annee_academique_id' => $annee?->id]);

        User::updateOrCreate(['email' => 'etudiant@bibliotheque.univ'], [
            'name' => 'Étudiant Démo',
            'prenom' => 'Awa',
            'password' => 'password',
            'role' => User::ROLE_ETUDIANT,
            'matricule' => 'ETU0001',
            'filiere' => 'Informatique',
            'niveau' => 'Licence 3',
            'faculte' => 'Faculté des Sciences et Technologies',
            'annee_academique_id' => $annee?->id,
            'statut' => 'actif',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->count(96)->create(['annee_academique_id' => $annee?->id]);
        User::factory()->count(2)->suspendu()->create(['annee_academique_id' => $annee?->id]);
        User::factory()->count(1)->create(['statut' => 'diplome', 'annee_academique_id' => $annee?->id]);

        $this->command?->info('Utilisateurs créés : '.User::count().' comptes.');
    }
}
