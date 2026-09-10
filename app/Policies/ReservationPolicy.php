<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('reservations.voir') || $user->estEmprunteur();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->peut('reservations.voir') || $reservation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->peut('reservations.gerer') || ($user->estEmprunteur() && $user->estActif());
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->peut('reservations.gerer');
    }

    public function annuler(User $user, Reservation $reservation): bool
    {
        return $user->peut('reservations.gerer') || $reservation->user_id === $user->id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->peut('reservations.gerer');
    }
}
