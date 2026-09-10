<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpirerReservations extends Command
{
    protected $signature = 'bibliotheque:expirer-reservations';

    protected $description = "Expire les réservations non retirées et notifie l'usager suivant dans la file";

    public function handle(ReservationService $reservations): int
    {
        $nombre = $reservations->expirerReservationsDepassees();

        $this->info("Réservations expirées : {$nombre}");

        return self::SUCCESS;
    }
}
