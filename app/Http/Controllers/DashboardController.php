<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Models\Emprunt;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'etudiant') {
            return $this->studentDashboard();
        }

        $stats = [
            'total_livres' => Livre::count(),
            'livres_disponibles' => Livre::where('statut', 'disponible')->count(),
            'total_etudiants' => User::where('role', 'etudiant')->count(),
            'emprunts_en_cours' => Emprunt::where('statut', 'en cours')->count(),
            'emprunts_en_retard' => Emprunt::where('statut', 'en retard')->count(),
            'reservations_actives' => Reservation::where('statut', 'active')->count(),
            'total_amendes' => Emprunt::where('statut', 'en retard')
                ->orWhere(function($query) {
                    $query->where('statut', 'en cours')
                          ->where('date_retour_prevue', '<', now());
                })
                ->get()
                ->sum('montant_amende'),
        ];

        // Flux des 7 derniers jours
        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chart_labels[] = $date->translatedFormat('D d');
            $chart_data[] = Emprunt::whereDate('date_emprunt', $date->format('Y-m-d'))->count();
        }

        // Statistiques par catégorie (pour doughnut)
        $categories_stats = Livre::select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')
            ->orderBy('total', 'desc')
            ->get();

        // Emprunts récents & Retards prioritaires
        $emprunts_recents = Emprunt::with(['user', 'livre'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $retards_critiques = Emprunt::with(['user', 'livre'])
            ->where('statut', 'en retard')
            ->orWhere(function($query) {
                $query->where('statut', 'en cours')
                      ->where('date_retour_prevue', '<', now());
            })
            ->orderBy('date_retour_prevue', 'asc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 
            'emprunts_recents', 
            'categories_stats', 
            'chart_labels', 
            'chart_data',
            'retards_critiques'
        ));
    }

    public function statistiques()
    {
        // Statistiques mensuelles d'emprunts
        $emprunts_mensuels = Emprunt::select(
                DB::raw('MONTH(date_emprunt) as mois'),
                DB::raw('YEAR(date_emprunt) as annee'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('date_emprunt', date('Y'))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        // Top 10 des livres les plus empruntés
        $top_livres = Livre::withCount('emprunts')
            ->orderBy('emprunts_count', 'desc')
            ->take(10)
            ->get();

        // Top 10 des étudiants les plus actifs
        $top_etudiants = User::where('role', 'etudiant')
            ->withCount('emprunts')
            ->orderBy('emprunts_count', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.statistiques', compact('emprunts_mensuels', 'top_livres', 'top_etudiants'));
    }

    protected function studentDashboard()
    {
        $user = auth()->user();
        
        $stats = [
            'total_emprunts' => $user->emprunts()->count(),
            'emprunts_en_cours' => $user->emprunts()->where('statut', 'en cours')->count(),
            'emprunts_en_retard' => $user->emprunts()->where('statut', 'en retard')->count(),
            'total_amendes' => $user->emprunts()->sum('amende') + $user->emprunts()
                ->where('statut', 'en cours')
                ->where('date_retour_prevue', '<', now())
                ->get()
                ->sum('montant_amende'),
            'reservations_actives' => $user->reservations()->where('statut', 'active')->count(),
        ];

        $emprunts_recents = $user->emprunts()
            ->with('livre')
            ->orderBy('date_emprunt', 'desc')
            ->take(5)
            ->get();

        $reservations_recentes = $user->reservations()
            ->with('livre')
            ->where('statut', 'active')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard.student', compact('stats', 'emprunts_recents', 'reservations_recentes'));
    }
}