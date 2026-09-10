<?php

namespace Database\Factories;

use App\Models\Editeur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Editeur>
 */
class EditeurFactory extends Factory
{
    protected $model = Editeur::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->company(),
            'pays' => fake()->randomElement(['France', 'Côte d\'Ivoire', 'Sénégal', 'Canada', 'Belgique']),
            'site_web' => fake()->url(),
            'email' => fake()->companyEmail(),
        ];
    }
}
