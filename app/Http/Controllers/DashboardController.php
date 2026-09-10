<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Services\StatistiqueService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private readonly StatistiqueService $statistiques) {}

    public function index()
    {
        $user = Auth::user();

        if ($user->estEmprunteur()) {
            return $this->tableauDeBordUsager();
        }

        $stats = $this->statistiques->indicateursGlobaux();
        $flux = $this->statistiques->fluxQuotidien(7);

        return view('dashboard.index', [
            'stats' => $stats,
            'chart_labels' => $flux['labels'],
            'chart_data' => $flux['emprunts'],
            'chart_retours' => $flux['retours'],
            'categories_stats' => $this->statistiques->repartitionCatalogue(),
            'emprunts_recents' => Emprunt::with(['user:id,name,prenom,matricule', 'livre:id,titre,auteur'])
                ->latest()->take(8)->get(),
            'retards_critiques' => $this->statistiques->retardsCritiques(5),
        ]);
    }

    /** Statistiques détaillées (graphiques annuels, tops). */
    public function statistiques()
    {
        $this->authorize('statistiques.voir');

        return view('dashboard.statistiques', [
            'stats' => $this->statistiques->indicateursGlobaux(),
            'flux_mensuel' => $this->statistiques->fluxMensuel(12),
            'top_livres' => $this->statistiques->livresPopulaires(10),
            'top_categories' => $this->statistiques->categoriesPopulaires(8),
            'top_usagers' => $this->statistiques->usagersActifs(10),
        ]);
    }

    protected function tableauDeBordUsager()
    {
        $user = Auth::user();

        return view('dashboard.student', [
            'stats' => $this->statistiques->indicateursUsager($user),
            'emprunts_en_cours' => $user->emprunts()->with('livre:id,titre,auteur,image_couverture')
                ->enCours()->orderBy('date_retour_prevue')->take(6)->get(),
            'emprunts_recents' => $user->emprunts()->with('livre:id,titre,auteur,image_couverture')
                ->latest('date_emprunt')->take(5)->get(),
            'reservations_recentes' => $user->reservations()->with('livre:id,titre,auteur,image_couverture')
                ->actives()->latest()->take(3)->get(),
            'penalites' => $user->penalites()->bloquantes()->with('emprunt.livre:id,titre')->take(5)->get(),
            'recommandations' => $this->statistiques->recommandationsPour($user, 6),
        ]);
    }
}
