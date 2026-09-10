<?php

namespace Database\Factories;

use App\Models\Exemplaire;
use App\Models\Livre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exemplaire>
 */
class ExemplaireFactory extends Factory
{
    protected $model = Exemplaire::class;

    public function definition(): array
    {
        return [
            'livre_id' => Livre::factory(),
            'code_barre' => strtoupper(fake()->unique()->bothify('EX-####-???')),
            'etat' => fake()->randomElement(array_keys(Exemplaire::ETATS)),
            'statut' => Exemplaire::STATUT_DISPONIBLE,
            'date_acquisition' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'prix_achat' => fake()->numberBetween(5000, 60000),
        ];
    }

    public function emprunte(): static
    {
        return $this->state(fn () => ['statut' => Exemplaire::STATUT_EMPRUNTE]);
    }

    public function perdu(): static
    {
        return $this->state(fn () => ['statut' => Exemplaire::STATUT_PERDU]);
    }
}
