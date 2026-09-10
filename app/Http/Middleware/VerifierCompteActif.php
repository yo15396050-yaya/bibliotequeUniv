<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un compte désactivé, suspendu ou radié est immédiatement déconnecté :
 * la désactivation prend effet sans attendre l'expiration de la session.
 */
class VerifierCompteActif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->estActif()) {
            $motif = $user->actif
                ? "Votre compte est « {$user->libelle_statut} »."
                : 'Votre compte a été désactivé.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', $motif.' Contactez la bibliothèque pour plus d\'informations.');
        }

        return $next($request);
    }
}
