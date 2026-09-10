<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    private const FILIERES = [
        'Informatique', 'Mathématiques', 'Droit', 'Économie', 'Gestion',
        'Médecine', 'Sciences physiques', 'Lettres modernes', 'Philosophie', 'Génie civil',
    ];

    private const NIVEAUX = ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2', 'Doctorat'];

    private const FACULTES = [
        'Faculté des Sciences et Technologies',
        'Faculté de Droit et Sciences Politiques',
        'Faculté des Sciences Économiques et de Gestion',
        'Faculté de Médecine',
        'Faculté des Lettres et Sciences Humaines',
    ];

    public function definition(): array
    {
        $prenom = fake()->firstName();
        $nom = fake()->lastName();

        return [
            'name' => $prenom.' '.$nom,
            'prenom' => $prenom,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
            'role' => User::ROLE_ETUDIANT,
            'matricule' => strtoupper(fake()->unique()->bothify('ETU-####??')),
            'telephone' => '07'.fake()->numerify('########'),
            'adresse' => fake()->streetAddress(),
            'date_naissance' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'faculte' => fake()->randomElement(self::FACULTES),
            'departement' => fake()->randomElement(self::FILIERES),
            'filiere' => fake()->randomElement(self::FILIERES),
            'niveau' => fake()->randomElement(self::NIVEAUX),
            'statut' => 'actif',
            'actif' => true,
            'nombre_emprunts' => 0,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_ADMIN,
            'matricule' => strtoupper(fake()->unique()->bothify('ADM-####')),
            'filiere' => null,
            'niveau' => null,
        ]);
    }

    public function bibliothecaire(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_BIBLIOTHECAIRE,
            'matricule' => strtoupper(fake()->unique()->bothify('BIB-####')),
            'filiere' => null,
            'niveau' => null,
        ]);
    }

    public function enseignant(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_ENSEIGNANT,
            'matricule' => strtoupper(fake()->unique()->bothify('ENS-####')),
            'grade' => fake()->randomElement(['Assistant', 'Maître-assistant', 'Maître de conférences', 'Professeur']),
            'niveau' => null,
        ]);
    }

    public function suspendu(): static
    {
        return $this->state(fn () => ['statut' => 'suspendu']);
    }

    public function inactif(): static
    {
        return $this->state(fn () => ['actif' => false, 'statut' => 'suspendu']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
