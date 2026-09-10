<?php

namespace App\Console\Commands;

use App\Models\Livre;
use Illuminate\Console\Command;

class SynchroniserCompteurs extends Command
{
    protected $signature = 'bibliotheque:synchroniser-compteurs';

    protected $description = 'Recalcule les compteurs d\'exemplaires de chaque notice à partir des exemplaires réels';

    public function handle(): int
    {
        $traites = 0;
        $barre = $this->output->createProgressBar(Livre::count());

        Livre::chunkById(200, function ($livres) use (&$traites, $barre) {
            foreach ($livres as $livre) {
                $livre->synchroniserCompteurs();
                $traites++;
                $barre->advance();
            }
        });

        $barre->finish();
        $this->newLine();
        $this->info("Notices synchronisées : {$traites}");

        return self::SUCCESS;
    }
}
