<?php

namespace Database\Seeders;

use App\Models\Parametre;
use App\Support\Parametres;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Parametres::DEFAUTS as $cle => $meta) {
            Parametre::updateOrCreate(['cle' => $cle], [
                'valeur' => is_bool($meta['valeur'])
                    ? ($meta['valeur'] ? '1' : '0')
                    : (string) $meta['valeur'],
                'type' => $meta['type'],
                'groupe' => $meta['groupe'],
                'libelle' => $meta['libelle'],
                'description' => $meta['description'] ?? null,
            ]);
        }

        Parametres::viderCache();

        $this->command?->info('Paramètres métier initialisés ('.count(Parametres::DEFAUTS).' clés).');
    }
}
