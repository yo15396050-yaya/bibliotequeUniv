<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\ParametreSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Charge le socle indispensable : rôles, permissions et règles métier.
     * À appeler dans le setUp() des tests qui utilisent RefreshDatabase.
     */
    protected function preparerSocle(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(ParametreSeeder::class);
    }

    protected function admin(array $attributs = []): User
    {
        return User::factory()->admin()->create($attributs);
    }

    protected function bibliothecaire(array $attributs = []): User
    {
        return User::factory()->bibliothecaire()->create($attributs);
    }

    protected function etudiant(array $attributs = []): User
    {
        return User::factory()->create($attributs);
    }

    protected function enseignant(array $attributs = []): User
    {
        return User::factory()->enseignant()->create($attributs);
    }
}
