<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Crée les rôles, les permissions et la matrice d'attribution.
 * Idempotent : peut être rejoué sans dupliquer les données.
 */
class RbacSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permissions::CATALOGUE as $module => $permissions) {
            foreach ($permissions as $nom => $libelle) {
                Permission::updateOrCreate(['nom' => $nom], [
                    'libelle' => $libelle,
                    'module' => $module,
                ]);
            }
        }

        $descriptions = [
            User::ROLE_ADMIN => 'Accès complet à toutes les fonctionnalités et aux paramètres du système.',
            User::ROLE_BIBLIOTHECAIRE => 'Gestion quotidienne : catalogue, usagers, circulation, pénalités.',
            User::ROLE_ENSEIGNANT => 'Consultation du catalogue, emprunts et réservations avec règles étendues.',
            User::ROLE_ETUDIANT => 'Consultation du catalogue, emprunts, réservations et suivi personnel.',
        ];

        foreach (User::ROLES as $nom => $libelle) {
            $role = Role::updateOrCreate(['nom' => $nom], [
                'libelle' => $libelle,
                'description' => $descriptions[$nom] ?? null,
                'systeme' => true,
            ]);

            $ids = Permission::whereIn('nom', Permissions::pourRole($nom))->pluck('id');
            $role->permissions()->sync($ids);
        }

        Cache::forget('rbac.permissions_par_role');

        $this->command?->info('Rôles et permissions initialisés.');
    }
}
