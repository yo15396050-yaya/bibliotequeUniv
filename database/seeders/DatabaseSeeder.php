<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Livre;
use App\Models\Emprunt;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Créer les utilisateurs
        $admin = User::create([
            'name' => 'ouattara yaya',
            'email' => 'ouattarayaya@gmail.com',
            'password' => Hash::make('yaya1539'),
            'role' => 'admin',
            'matricule' => 'YAYA001',
            'telephone' => '0712491576'
        
        ]);

        $bibliothecaire = User::create([
            'name' => 'Bibliothécaire',
            'email' => 'biblio@bibliotheque.univ',
            'password' => Hash::make('password'),
            'role' => 'bibliothecaire',
            'matricule' => 'BIBLIO001',
            'telephone' => '0700000001',
        ]);
        

        // Créer 10 étudiants
        $etudiants = User::factory()->count(10)->create([
            'role' => 'etudiant',
            'actif' => true,
        ]);

        // Créer des livres
        $livres = Livre::factory()->count(50)->create();

        // Créer des emprunts pour certains livres
        foreach ($livres->take(20) as $livre) {
            $etudiant = $etudiants->random();
            
            Emprunt::create([
                'user_id' => $etudiant->id,
                'livre_id' => $livre->id,
                'date_emprunt' => now()->subDays(rand(1, 30)),
                'date_retour_prevue' => now()->addDays(rand(1, 15)),
                'statut' => 'en cours',
            ]);

            $livre->exemplaires_disponibles = $livre->exemplaires_disponibles - 1;
            $livre->statut = $livre->exemplaires_disponibles > 0 ? 'disponible' : 'emprunté';
            $livre->save();

            $etudiant->increment('nombre_emprunts');
        }

        $this->command->info('Base de données peuplée avec succès !');
    }
}