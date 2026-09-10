<?php

namespace App\Support;

use App\Models\Parametre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Accès centralisé aux règles métier configurables.
 *
 * Toutes les règles (durées, quotas, montants, délais) transitent par ici :
 * aucune valeur « en dur » ne doit rester dans les services ou les vues.
 */
class Parametres
{
    /** Valeurs par défaut utilisées tant qu'aucun paramètre n'est enregistré. */
    public const DEFAUTS = [
        // Emprunts
        'emprunt.duree_etudiant' => ['valeur' => 14, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Durée d'emprunt étudiant (jours)"],
        'emprunt.duree_enseignant' => ['valeur' => 30, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Durée d'emprunt enseignant (jours)"],
        'emprunt.max_etudiant' => ['valeur' => 3, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Nombre maximal d'emprunts simultanés (étudiant)"],
        'emprunt.max_enseignant' => ['valeur' => 10, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Nombre maximal d'emprunts simultanés (enseignant)"],
        'emprunt.max_personnel' => ['valeur' => 5, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Nombre maximal d'emprunts simultanés (personnel)"],
        'emprunt.jours_alerte_echeance' => ['valeur' => 3, 'type' => 'integer', 'groupe' => 'emprunt', 'libelle' => "Alerter l'usager X jours avant l'échéance"],

        // Renouvellements
        'renouvellement.max_etudiant' => ['valeur' => 1, 'type' => 'integer', 'groupe' => 'renouvellement', 'libelle' => 'Renouvellements maximum (étudiant)'],
        'renouvellement.max_enseignant' => ['valeur' => 3, 'type' => 'integer', 'groupe' => 'renouvellement', 'libelle' => 'Renouvellements maximum (enseignant)'],
        'renouvellement.validation_requise' => ['valeur' => false, 'type' => 'boolean', 'groupe' => 'renouvellement', 'libelle' => 'Le renouvellement doit être validé par un bibliothécaire'],

        // Pénalités
        'penalite.montant_par_jour' => ['valeur' => 100, 'type' => 'decimal', 'groupe' => 'penalite', 'libelle' => 'Montant de la pénalité par jour de retard (FCFA)'],
        'penalite.plafond_retard' => ['valeur' => 10000, 'type' => 'decimal', 'groupe' => 'penalite', 'libelle' => 'Plafond de la pénalité de retard (FCFA, 0 = sans plafond)'],
        'penalite.montant_perte' => ['valeur' => 25000, 'type' => 'decimal', 'groupe' => 'penalite', 'libelle' => 'Montant forfaitaire pour un livre perdu (FCFA)'],
        'penalite.montant_degradation' => ['valeur' => 10000, 'type' => 'decimal', 'groupe' => 'penalite', 'libelle' => 'Montant forfaitaire pour un livre endommagé (FCFA)'],
        'penalite.seuil_blocage' => ['valeur' => 1000, 'type' => 'decimal', 'groupe' => 'penalite', 'libelle' => 'Dette à partir de laquelle les emprunts sont bloqués (FCFA)'],
        'penalite.jours_grace' => ['valeur' => 0, 'type' => 'integer', 'groupe' => 'penalite', 'libelle' => 'Jours de grâce avant application de la pénalité'],

        // Réservations
        'reservation.duree_validite' => ['valeur' => 7, 'type' => 'integer', 'groupe' => 'reservation', 'libelle' => 'Durée de validité d\'une réservation (jours)'],
        'reservation.delai_retrait' => ['valeur' => 2, 'type' => 'integer', 'groupe' => 'reservation', 'libelle' => 'Délai de retrait après notification (jours)'],
        'reservation.max_par_usager' => ['valeur' => 3, 'type' => 'integer', 'groupe' => 'reservation', 'libelle' => 'Réservations actives maximum par usager'],

        // Établissement
        'general.nom_bibliotheque' => ['valeur' => 'Bibliothèque Universitaire', 'type' => 'string', 'groupe' => 'general', 'libelle' => 'Nom de la bibliothèque'],
        'general.universite' => ['valeur' => 'Université', 'type' => 'string', 'groupe' => 'general', 'libelle' => "Nom de l'université"],
        'general.email_contact' => ['valeur' => 'bibliotheque@univ.edu', 'type' => 'string', 'groupe' => 'general', 'libelle' => 'Email de contact'],
        'general.telephone' => ['valeur' => '+225 00 00 00 00', 'type' => 'string', 'groupe' => 'general', 'libelle' => 'Téléphone'],
        'general.devise' => ['valeur' => 'FCFA', 'type' => 'string', 'groupe' => 'general', 'libelle' => 'Devise'],

        // Notifications
        'notification.email_active' => ['valeur' => true, 'type' => 'boolean', 'groupe' => 'notification', 'libelle' => 'Envoyer les notifications par email'],
        'notification.interne_active' => ['valeur' => true, 'type' => 'boolean', 'groupe' => 'notification', 'libelle' => 'Activer les notifications internes'],

        // Documents numériques
        'document.formats_autorises' => ['valeur' => 'pdf,epub,docx', 'type' => 'string', 'groupe' => 'document', 'libelle' => 'Formats de fichiers autorisés'],
        'document.taille_max_mo' => ['valeur' => 50, 'type' => 'integer', 'groupe' => 'document', 'libelle' => 'Taille maximale d\'un document (Mo)'],
        'document.duree_lien_temporaire' => ['valeur' => 10, 'type' => 'integer', 'groupe' => 'document', 'libelle' => 'Durée de validité d\'un lien de téléchargement (minutes)'],
    ];

    /** Toutes les valeurs typées, indexées par clé. */
    public static function toutes(): array
    {
        if (! Schema::hasTable('parametres')) {
            return static::valeursParDefaut();
        }

        return Cache::rememberForever(Parametre::CACHE_KEY, function () {
            $enBase = Parametre::all()->mapWithKeys(
                fn (Parametre $p) => [$p->cle => $p->valeur_typee]
            )->all();

            return array_merge(static::valeursParDefaut(), $enBase);
        });
    }

    public static function get(string $cle, mixed $defaut = null): mixed
    {
        return static::toutes()[$cle] ?? $defaut ?? static::defautDe($cle);
    }

    public static function entier(string $cle, int $defaut = 0): int
    {
        $valeur = static::toutes()[$cle] ?? static::defautDe($cle);

        return $valeur === null ? $defaut : (int) $valeur;
    }

    public static function decimal(string $cle, float $defaut = 0): float
    {
        $valeur = static::toutes()[$cle] ?? static::defautDe($cle);

        return $valeur === null ? $defaut : (float) $valeur;
    }

    public static function booleen(string $cle, bool $defaut = false): bool
    {
        $valeur = static::toutes()[$cle] ?? static::defautDe($cle);

        return $valeur === null ? $defaut : filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
    }

    public static function chaine(string $cle, string $defaut = ''): string
    {
        $valeur = static::toutes()[$cle] ?? static::defautDe($cle);

        return $valeur === null ? $defaut : (string) $valeur;
    }

    /** Enregistre (ou crée) un paramètre et vide le cache. */
    public static function definir(string $cle, mixed $valeur): Parametre
    {
        $meta = static::DEFAUTS[$cle] ?? ['type' => 'string', 'groupe' => 'general', 'libelle' => $cle];

        $parametre = Parametre::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => is_bool($valeur) ? ($valeur ? '1' : '0') : (is_array($valeur) ? json_encode($valeur) : (string) $valeur),
                'type' => $meta['type'],
                'groupe' => $meta['groupe'],
                'libelle' => $meta['libelle'],
            ]
        );

        Cache::forget(Parametre::CACHE_KEY);

        return $parametre;
    }

    public static function viderCache(): void
    {
        Cache::forget(Parametre::CACHE_KEY);
    }

    public static function devise(): string
    {
        return static::chaine('general.devise', 'FCFA');
    }

    /** Formate un montant avec la devise configurée. */
    public static function formaterMontant(float|int|string|null $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ').' '.static::devise();
    }

    private static function defautDe(string $cle): mixed
    {
        return static::DEFAUTS[$cle]['valeur'] ?? null;
    }

    private static function valeursParDefaut(): array
    {
        return array_map(fn (array $meta) => $meta['valeur'], static::DEFAUTS);
    }
}
