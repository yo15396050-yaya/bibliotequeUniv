<?php

namespace Database\Factories;

use App\Models\Auteur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auteur>
 */
class AuteurFactory extends Factory
{
    protected $model = Auteur::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'biographie' => fake()->paragraph(3),
            'nationalite' => fake()->randomElement([
                'Ivoirienne', 'Française', 'Sénégalaise', 'Marocaine',
                'Canadienne', 'Belge', 'Américaine', 'Britannique',
            ]),
            'date_naissance' => fake()->dateTimeBetween('-90 years', '-30 years')->format('Y-m-d'),
        ];
    }
}
