<?php

namespace App\Support;

use App\Models\User;

/**
 * Catalogue des permissions de l'application et matrice rôle → permissions.
 * Sert de source unique au seeder RBAC et à l'écran d'administration.
 */
class Permissions
{
    /** @var array<string, array<string, string>> module => [permission => libellé] */
    public const CATALOGUE = [
        'catalogue' => [
            'livres.voir' => 'Consulter le catalogue',
            'livres.creer' => 'Ajouter un ouvrage',
            'livres.modifier' => 'Modifier un ouvrage',
            'livres.supprimer' => 'Supprimer un ouvrage',
            'auteurs.gerer' => 'Gérer les auteurs',
            'editeurs.gerer' => 'Gérer les éditeurs',
            'categories.gerer' => 'Gérer les catégories',
        ],
        'exemplaires' => [
            'exemplaires.voir' => 'Consulter les exemplaires',
            'exemplaires.gerer' => 'Gérer les exemplaires',
            'localisations.gerer' => 'Gérer les rayons et emplacements',
        ],
        'circulation' => [
            'emprunts.voir' => 'Consulter les emprunts',
            'emprunts.enregistrer' => 'Enregistrer un emprunt',
            'emprunts.retour' => 'Enregistrer un retour',
            'emprunts.renouveler' => 'Renouveler un emprunt',
            'reservations.voir' => 'Consulter les réservations',
            'reservations.gerer' => 'Gérer les réservations',
        ],
        'penalites' => [
            'penalites.voir' => 'Consulter les pénalités',
            'penalites.gerer' => 'Créer et annuler des pénalités',
            'penalites.encaisser' => 'Encaisser un paiement',
        ],
        'usagers' => [
            'usagers.voir' => 'Consulter les usagers',
            'usagers.creer' => 'Créer un usager',
            'usagers.modifier' => 'Modifier un usager',
            'usagers.supprimer' => 'Supprimer un usager',
            'usagers.roles' => 'Modifier les rôles et permissions',
        ],
        'documents' => [
            'documents.voir' => 'Consulter les documents numériques',
            'documents.gerer' => 'Ajouter ou supprimer des documents numériques',
        ],
        'administration' => [
            'statistiques.voir' => 'Consulter les statistiques',
            'rapports.generer' => 'Générer et exporter les rapports',
            'parametres.gerer' => 'Modifier les paramètres du système',
            'audit.voir' => "Consulter le journal d'activité",
        ],
    ];

    /** @var array<string, array<int, string>|string> rôle => permissions ('*' = toutes) */
    public const MATRICE = [
        User::ROLE_ADMIN => '*',

        User::ROLE_BIBLIOTHECAIRE => [
            'livres.voir', 'livres.creer', 'livres.modifier',
            'auteurs.gerer', 'editeurs.gerer', 'categories.gerer',
            'exemplaires.voir', 'exemplaires.gerer', 'localisations.gerer',
            'emprunts.voir', 'emprunts.enregistrer', 'emprunts.retour', 'emprunts.renouveler',
            'reservations.voir', 'reservations.gerer',
            'penalites.voir', 'penalites.gerer', 'penalites.encaisser',
            'usagers.voir', 'usagers.creer', 'usagers.modifier',
            'documents.voir', 'documents.gerer',
            'statistiques.voir', 'rapports.generer',
        ],

        User::ROLE_ENSEIGNANT => [
            'livres.voir', 'exemplaires.voir', 'documents.voir',
        ],

        User::ROLE_ETUDIANT => [
            'livres.voir', 'exemplaires.voir', 'documents.voir',
        ],
    ];

    /** @return array<int, string> toutes les permissions à plat */
    public static function toutes(): array
    {
        return array_keys(array_merge(...array_values(self::CATALOGUE)));
    }

    /** @return array<int, string> permissions d'un rôle */
    public static function pourRole(string $role): array
    {
        $permissions = self::MATRICE[$role] ?? [];

        return $permissions === '*' ? self::toutes() : $permissions;
    }

    public static function libelle(string $permission): string
    {
        foreach (self::CATALOGUE as $module) {
            if (isset($module[$permission])) {
                return $module[$permission];
            }
        }

        return $permission;
    }

    public static function module(string $permission): string
    {
        foreach (self::CATALOGUE as $module => $permissions) {
            if (isset($permissions[$permission])) {
                return $module;
            }
        }

        return 'general';
    }
}
