<?php

namespace App\Services;

use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Agrégats des tableaux de bord et des rapports.
 * Les regroupements par période utilisent des intervalles de dates plutôt que
 * des fonctions SQL propriétaires (MONTH(), YEAR()) : le code reste portable
 * entre MySQL et SQLite (tests).
 */
class StatistiqueService
{
    /** Indicateurs de la bibliothèque. */
    public function indicateursGlobaux(): array
    {
        $exemplairesParStatut = Exemplaire::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')->pluck('total', 'statut');

        $totalExemplaires = (int) $exemplairesParStatut->sum();

        return [
            'total_livres' => Livre::count(),
            'total_exemplaires' => $totalExemplaires ?: (int) Livre::sum('exemplaires_totaux'),
            'exemplaires_disponibles' => (int) ($exemplairesParStatut[Exemplaire::STATUT_DISPONIBLE]
                ?? Livre::sum('exemplaires_disponibles')),
            'exemplaires_empruntes' => (int) ($exemplairesParStatut[Exemplaire::STATUT_EMPRUNTE]
                ?? Emprunt::enCours()->count()),
            'exemplaires_reserves' => (int) ($exemplairesParStatut[Exemplaire::STATUT_RESERVE] ?? 0),
            'exemplaires_perdus' => (int) ($exemplairesParStatut[Exemplaire::STATUT_PERDU] ?? 0),
            'exemplaires_endommages' => (int) ($exemplairesParStatut[Exemplaire::STATUT_ENDOMMAGE] ?? 0),
            'livres_disponibles' => Livre::disponibles()->count(),
            'total_etudiants' => User::role(User::ROLE_ETUDIANT)->count(),
            'etudiants_actifs' => User::role(User::ROLE_ETUDIANT)->actifs()->count(),
            'total_enseignants' => User::role(User::ROLE_ENSEIGNANT)->count(),
            'emprunts_en_cours' => Emprunt::enCours()->count(),
            'emprunts_en_retard' => Emprunt::enRetard()->count(),
            'reservations_actives' => Reservation::actives()->count(),
            'penalites_impayees' => (float) Penalite::bloquantes()->sum('montant')
                - (float) Penalite::bloquantes()->sum('montant_paye'),
            'nombre_penalites_impayees' => Penalite::bloquantes()->count(),
        ];
    }

    /** Emprunts / retours par jour sur les N derniers jours. */
    public function fluxQuotidien(int $jours = 7): array
    {
        $labels = [];
        $emprunts = [];
        $retours = [];

        for ($i = $jours - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('D d/m');
            $emprunts[] = Emprunt::whereDate('date_emprunt', $date->toDateString())->count();
            $retours[] = Emprunt::whereDate('date_retour_effective', $date->toDateString())->count();
        }

        return ['labels' => $labels, 'emprunts' => $emprunts, 'retours' => $retours];
    }

    /** Emprunts / retours / retards par mois sur les N derniers mois. */
    public function fluxMensuel(int $mois = 12): array
    {
        $labels = [];
        $emprunts = [];
        $retours = [];
        $retards = [];

        for ($i = $mois - 1; $i >= 0; $i--) {
            $debut = now()->startOfMonth()->subMonths($i);
            $fin = $debut->copy()->endOfMonth();

            $labels[] = $debut->translatedFormat('M Y');
            $emprunts[] = Emprunt::whereBetween('date_emprunt', [$debut->toDateString(), $fin->toDateString()])->count();
            $retours[] = Emprunt::whereBetween('date_retour_effective', [$debut->toDateString(), $fin->toDateString()])->count();
            $retards[] = Emprunt::whereBetween('date_retour_effective', [$debut->toDateString(), $fin->toDateString()])
                ->whereColumn('date_retour_effective', '>', 'date_retour_prevue')->count();
        }

        return ['labels' => $labels, 'emprunts' => $emprunts, 'retours' => $retours, 'retards' => $retards];
    }

    /** Livres les plus empruntés. */
    public function livresPopulaires(int $limite = 10)
    {
        // `has()` plutôt que `having()` : la clause HAVING sur une sous-requête
        // agrégée n'est pas portable (rejetée par SQLite).
        return Livre::withCount('emprunts')
            ->has('emprunts')
            ->orderByDesc('emprunts_count')
            ->take($limite)
            ->get(['id', 'titre', 'auteur', 'categorie', 'image_couverture']);
    }

    /** Catégories les plus demandées (basé sur les emprunts). */
    public function categoriesPopulaires(int $limite = 8)
    {
        return Emprunt::query()
            ->join('livres', 'livres.id', '=', 'emprunts.livre_id')
            ->select('livres.categorie', DB::raw('count(*) as total'))
            ->groupBy('livres.categorie')
            ->orderByDesc('total')
            ->take($limite)
            ->get();
    }

    /** Répartition du catalogue par catégorie. */
    public function repartitionCatalogue(int $limite = 8)
    {
        return Livre::select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->take($limite)
            ->get();
    }

    /** Usagers les plus actifs. */
    public function usagersActifs(int $limite = 10)
    {
        return User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])
            ->withCount('emprunts')
            ->has('emprunts')
            ->orderByDesc('emprunts_count')
            ->take($limite)
            ->get(['id', 'name', 'prenom', 'matricule', 'role', 'filiere', 'photo']);
    }

    /** Retards les plus critiques (échéance la plus ancienne). */
    public function retardsCritiques(int $limite = 5)
    {
        return Emprunt::with(['user:id,name,prenom,matricule,email', 'livre:id,titre,auteur'])
            ->enRetard()
            ->orderBy('date_retour_prevue')
            ->take($limite)
            ->get();
    }

    /** Tableau de bord d'un usager. */
    public function indicateursUsager(User $user): array
    {
        $penalites = $user->penalites()->bloquantes();

        return [
            'total_emprunts' => $user->emprunts()->count(),
            'emprunts_en_cours' => $user->emprunts()->enCours()->count(),
            'emprunts_en_retard' => $user->emprunts()->enRetard()->count(),
            'a_retourner_bientot' => $user->emprunts()->echeanceProche()->count(),
            'reservations_actives' => $user->reservations()->actives()->count(),
            'penalites_impayees' => (float) $penalites->sum('montant') - (float) $penalites->sum('montant_paye'),
            'quota' => $user->quotaEmprunts(),
        ];
    }

    /**
     * Recommandations : ouvrages disponibles des catégories déjà empruntées
     * par l'usager, complétées par les plus populaires.
     */
    public function recommandationsPour(User $user, int $limite = 6)
    {
        $categories = Emprunt::where('emprunts.user_id', $user->id)
            ->join('livres', 'livres.id', '=', 'emprunts.livre_id')
            ->distinct()->pluck('livres.categorie')->filter()->all();

        $dejaLus = $user->emprunts()->pluck('livre_id')->all();

        $recommandations = Livre::disponibles()
            ->when($categories, fn ($q) => $q->whereIn('categorie', $categories))
            ->whereNotIn('id', $dejaLus)
            ->withCount('emprunts')
            ->orderByDesc('emprunts_count')
            ->take($limite)
            ->get();

        if ($recommandations->count() < $limite) {
            $complement = Livre::disponibles()
                ->whereNotIn('id', array_merge($dejaLus, $recommandations->pluck('id')->all()))
                ->withCount('emprunts')
                ->orderByDesc('emprunts_count')
                ->take($limite - $recommandations->count())
                ->get();

            $recommandations = $recommandations->concat($complement);
        }

        return $recommandations;
    }
}
