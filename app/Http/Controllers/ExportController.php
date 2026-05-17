<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Models\Emprunt;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Exporte l'inventaire complet des livres en PDF
     */
    public function inventaire()
    {
        $livres = Livre::orderBy('titre')->get();
        $date = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('exports.inventory', compact('livres', 'date'));
        
        return $pdf->download('inventaire_bibliotheque_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporte le rapport des retards et des amendes en PDF
     */
    public function retards()
    {
        $retards = Emprunt::with(['user', 'livre'])
            ->where('statut', 'en retard')
            ->orWhere(function($query) {
                $query->where('statut', 'en cours')
                      ->where('date_retour_prevue', '<', now());
            })
            ->get();
            
        $date = now()->format('d/m/Y');
        $totalAmendes = $retards->sum(fn($r) => $r->montant_amende);

        $pdf = Pdf::loadView('exports.retards', compact('retards', 'date', 'totalAmendes'));
        
        return $pdf->download('rapport_retards_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporte la fiche d'activité d'un étudiant en PDF
     */
    public function etudiant($id)
    {
        $etudiant = User::with(['emprunts.livre'])->findOrFail($id);
        $date = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('exports.etudiant', compact('etudiant', 'date'));
        
        return $pdf->download('fiche_activite_' . str_replace(' ', '_', $etudiant->name) . '.pdf');
    }
}
