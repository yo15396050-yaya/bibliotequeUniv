<?php

namespace App\Console\Commands;

use App\Models\Emprunt;
use App\Notifications\EcheanceProche;
use App\Notifications\RetardSignale;
use App\Services\EmpruntService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TraiterRetards extends Command
{
    protected $signature = 'bibliotheque:traiter-retards
                            {--sans-notification : Ne pas envoyer les rappels}';

    protected $description = 'Marque les emprunts échus en retard, génère les pénalités et envoie les rappels';

    public function handle(EmpruntService $emprunts, NotificationService $notifications): int
    {
        $resultat = $emprunts->traiterRetards();

        $this->info("Emprunts passés en retard : {$resultat['marques']}");
        $this->info("Pénalités générées ou mises à jour : {$resultat['penalites']}");

        if ($this->option('sans-notification')) {
            return self::SUCCESS;
        }

        $rappelsEcheance = 0;
        Emprunt::with(['user', 'livre'])->echeanceProche()
            ->chunkById(200, function ($lot) use ($notifications, &$rappelsEcheance) {
                foreach ($lot as $emprunt) {
                    $notifications->envoyer($emprunt->user, new EcheanceProche($emprunt));
                    $rappelsEcheance++;
                }
            });

        $rappelsRetard = 0;
        Emprunt::with(['user', 'livre'])->where('statut', Emprunt::STATUT_EN_RETARD)
            ->whereNull('date_retour_effective')
            ->chunkById(200, function ($lot) use ($notifications, &$rappelsRetard) {
                foreach ($lot as $emprunt) {
                    $notifications->envoyer($emprunt->user, new RetardSignale($emprunt));
                    $rappelsRetard++;
                }
            });

        $this->info("Rappels d'échéance envoyés : {$rappelsEcheance}");
        $this->info("Rappels de retard envoyés : {$rappelsRetard}");

        return self::SUCCESS;
    }
}
