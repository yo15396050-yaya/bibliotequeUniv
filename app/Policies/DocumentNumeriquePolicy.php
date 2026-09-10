<?php

namespace App\Policies;

use App\Models\DocumentNumerique;
use App\Models\User;

/**
 * Contrôle d'accès aux fichiers numériques. Aucun fichier n'est servi depuis
 * un répertoire public : la consultation et le téléchargement passent
 * systématiquement par cette policy.
 */
class DocumentNumeriquePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('documents.voir');
    }

    /** Droit de consulter (lire en ligne) le document. */
    public function view(User $user, DocumentNumerique $document): bool
    {
        if ($user->estPersonnel()) {
            return true;
        }

        if (! $user->estActif()) {
            return false;
        }

        return match ($document->visibilite) {
            'public', 'authentifie' => true,
            'etudiant' => $user->estEtudiant() || $user->estEnseignant(),
            'enseignant' => $user->estEnseignant(),
            'personnel' => false,
            default => false,
        };
    }

    /** Droit de télécharger le fichier. */
    public function download(User $user, DocumentNumerique $document): bool
    {
        if (! $this->view($user, $document)) {
            return false;
        }

        return $document->autoriser_telechargement || $user->estPersonnel();
    }

    public function create(User $user): bool
    {
        return $user->peut('documents.gerer');
    }

    public function delete(User $user, DocumentNumerique $document): bool
    {
        return $user->peut('documents.gerer');
    }
}
