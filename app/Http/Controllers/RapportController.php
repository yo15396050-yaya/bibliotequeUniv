<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index()
    {
        return view('rapports.index');
    }

    public function rapportEmprunts(Request $request)
    {
        $query = Emprunt::with(['user', 'livre']);
        
        // Filtrage par date
        if ($request->filled('date_debut')) {
            $query->where('date_emprunt', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_emprunt', '<=', $request->date_fin);
        }
        
        // Filtrage par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $emprunts = $query->orderBy('date_emprunt', 'desc')->get();
        
        // Statistiques
        $statistiques = [
            'total' => $emprunts->count(),
            'en_cours' => $emprunts->where('statut', 'en cours')->count(),
            'retournes' => $emprunts->where('statut', 'retourné')->count(),
            'en_retard' => $emprunts->where('statut', 'en retard')->count(),
            'total_amendes' => $emprunts->sum('amende'),
        ];
        
        return view('rapports.emprunts', compact('emprunts', 'statistiques'));
    }

    public function rapportLivres(Request $request)
    {
        $query = Livre::withCount(['emprunts', 'reservations']);
        
        // Filtrage par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        
        // Filtrage par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $livres = $query->orderBy('titre')->get();
        
        // Statistiques
        $statistiques = [
            'total' => $livres->count(),
            'disponibles' => $livres->where('statut', 'disponible')->count(),
            'empruntes' => $livres->where('statut', 'emprunté')->count(),
            'reserves' => $livres->where('statut', 'réservé')->count(),
            'total_exemplaires' => $livres->sum('exemplaires_totaux'),
            'exemplaires_disponibles' => $livres->sum('exemplaires_disponibles'),
        ];
        
        // Livres les plus populaires
        $livresPopulaires = $livres->sortByDesc(function($livre) {
            return $livre->emprunts_count;
        })->take(10);
        
        return view('rapports.livres', compact('livres', 'statistiques', 'livresPopulaires'));
    }

    public function rapportEtudiants(Request $request)
    {
        $query = User::where('role', 'etudiant')->withCount(['emprunts', 'reservations']);
        
        // Filtrage par filière
        if ($request->filled('filiere')) {
            $query->where('filiere', $request->filiere);
        }
        
        // Filtrage par statut (actif/inactif)
        if ($request->filled('actif')) {
            $query->where('actif', $request->actif === '1');
        }
        
        $etudiants = $query->orderBy('name')->get();
        
        // Statistiques
        $statistiques = [
            'total' => $etudiants->count(),
            'actifs' => $etudiants->where('actif', true)->count(),
            'inactifs' => $etudiants->where('actif', false)->count(),
            'total_emprunts' => $etudiants->sum('emprunts_count'),
            'total_reservations' => $etudiants->sum('reservations_count'),
        ];
        
        // Répartition par filière
        $repartitionFiliere = $etudiants->groupBy('filiere')->map(function($group) {
            return $group->count();
        });
        
        return view('rapports.etudiants', compact('etudiants', 'statistiques', 'repartitionFiliere'));
    }

    public function rapportRetards()
    {
        $empruntsEnRetard = Emprunt::with(['user', 'livre'])
                                  ->where('statut', 'en retard')
                                  ->orWhere(function($query) {
                                      $query->where('statut', 'en cours')
                                            ->where('date_retour_prevue', '<', now());
                                  })
                                  ->orderBy('date_retour_prevue')
                                  ->get();
        
        // Calculer les amendes
        foreach ($empruntsEnRetard as $emprunt) {
            $emprunt->amende_calculee = $emprunt->calculerAmende();
        }
        
        // Statistiques
        $statistiques = [
            'total_retards' => $empruntsEnRetard->count(),
            'total_amendes' => $empruntsEnRetard->sum('amende_calculee'),
            'retards_moins_7_jours' => $empruntsEnRetard->filter(function($e) {
                return now()->diffInDays($e->date_retour_prevue) <= 7;
            })->count(),
            'retards_plus_30_jours' => $empruntsEnRetard->filter(function($e) {
                return now()->diffInDays($e->date_retour_prevue) > 30;
            })->count(),
        ];
        
        return view('rapports.retards', compact('empruntsEnRetard', 'statistiques'));
    }

    public function exportPDF($type, Request $request)
    {
        switch ($type) {
            case 'emprunts':
                return $this->exportEmpruntsPDF($request);
            case 'livres':
                return $this->exportLivresPDF($request);
            case 'etudiants':
                return $this->exportEtudiantsPDF($request);
            case 'retards':
                return $this->exportRetardsPDF($request);
            default:
                return back()->with('error', 'Type de rapport invalide.');
        }
    }

    private function exportEmpruntsPDF($request)
    {
        $query = Emprunt::with(['user', 'livre']);
        
        if ($request->filled('date_debut')) {
            $query->where('date_emprunt', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_emprunt', '<=', $request->date_fin);
        }
        
        $emprunts = $query->orderBy('date_emprunt', 'desc')->get();
        
        $pdf = Pdf::loadView('rapports.pdf.emprunts', compact('emprunts'));
        return $pdf->download('rapport_emprunts.pdf');
    }

    private function exportLivresPDF($request)
    {
        $livres = Livre::withCount(['emprunts', 'reservations'])->get();
        
        $pdf = Pdf::loadView('rapports.pdf.livres', compact('livres'));
        return $pdf->download('rapport_livres.pdf');
    }

    private function exportEtudiantsPDF($request)
    {
        $etudiants = User::where('role', 'etudiant')
                        ->withCount(['emprunts', 'reservations'])
                        ->get();
        
        $pdf = Pdf::loadView('rapports.pdf.etudiants', compact('etudiants'));
        return $pdf->download('rapport_etudiants.pdf');
    }

    private function exportRetardsPDF($request)
    {
        $empruntsEnRetard = Emprunt::with(['user', 'livre'])
                                  ->where('statut', 'en retard')
                                  ->orWhere(function($query) {
                                      $query->where('statut', 'en cours')
                                            ->where('date_retour_prevue', '<', now());
                                  })
                                  ->orderBy('date_retour_prevue')
                                  ->get();
        
        $pdf = Pdf::loadView('rapports.pdf.retards', compact('empruntsEnRetard'));
        return $pdf->download('rapport_retards.pdf');
    }
}
