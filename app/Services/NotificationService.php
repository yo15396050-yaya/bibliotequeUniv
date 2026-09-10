<?php

namespace App\Services;

use App\Models\User;
use App\Support\Parametres;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Point d'entrée unique des notifications : respecte les canaux activés dans
 * les paramètres et n'interrompt jamais une opération métier en cas d'échec
 * (un serveur mail indisponible ne doit pas annuler un emprunt).
 */
class NotificationService
{
    public function envoyer(?User $user, Notification $notification): void
    {
        if (! $user) {
            return;
        }

        if (! Parametres::booleen('notification.interne_active', true)
            && ! Parametres::booleen('notification.email_active', true)) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('Notification non envoyée', [
                'user_id' => $user->id,
                'notification' => $notification::class,
                'erreur' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  iterable<User>  $users
     */
    public function envoyerPlusieurs(iterable $users, Notification $notification): int
    {
        $envoyees = 0;
        foreach ($users as $user) {
            $this->envoyer($user, $notification);
            $envoyees++;
        }

        return $envoyees;
    }

    /** Canaux actifs pour toutes les notifications de l'application. */
    public static function canaux(): array
    {
        $canaux = [];

        if (Parametres::booleen('notification.interne_active', true)) {
            $canaux[] = 'database';
        }

        if (Parametres::booleen('notification.email_active', true)) {
            $canaux[] = 'mail';
        }

        return $canaux ?: ['database'];
    }
}
