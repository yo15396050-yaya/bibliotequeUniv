<?php

namespace App\Services;

use App\Models\JournalActivite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Journalisation des opérations sensibles (création, modification, suppression,
 * emprunt, retour, réservation, paiement, changement de permission).
 */
class AuditService
{
    public function enregistrer(
        string $action,
        string $description,
        ?Model $sujet = null,
        array $donnees = [],
        string $module = 'general'
    ): JournalActivite {
        return JournalActivite::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'sujet_type' => $sujet ? $sujet::class : null,
            'sujet_id' => $sujet?->getKey(),
            'description' => mb_substr($description, 0, 255),
            'donnees' => $donnees ?: null,
            'adresse_ip' => Request::ip(),
            'user_agent' => mb_substr((string) Request::userAgent(), 0, 255),
        ]);
    }

    public function creation(Model $sujet, string $description, string $module = 'general'): JournalActivite
    {
        return $this->enregistrer('creation', $description, $sujet, $this->attributsUtiles($sujet), $module);
    }

    public function modification(Model $sujet, string $description, string $module = 'general'): JournalActivite
    {
        return $this->enregistrer('modification', $description, $sujet, [
            'modifications' => array_keys($sujet->getChanges()),
            'avant' => array_intersect_key($sujet->getOriginal(), $sujet->getChanges()),
        ], $module);
    }

    public function suppression(Model $sujet, string $description, string $module = 'general'): JournalActivite
    {
        return $this->enregistrer('suppression', $description, $sujet, $this->attributsUtiles($sujet), $module);
    }

    /** Retire les données sensibles avant journalisation. */
    private function attributsUtiles(Model $sujet): array
    {
        return collect($sujet->getAttributes())
            ->except(['password', 'remember_token'])
            ->take(25)
            ->all();
    }
}
