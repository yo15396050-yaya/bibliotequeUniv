<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use App\Support\Parametres;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Exports PDF « prêts à imprimer » (inventaire, retards, fiche usager).
 * Les rapports paramétrables et multi-formats sont dans RapportController.
 */
class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:rapports.generer')->except('etudiant');
    }

    public function inventaire()
    {
        $livres = Livre::withCount('exemplaires')->orderBy('titre')->get();
        $date = now()->format('d/m/Y');

        return Pdf::loadView('exports.inventory', compact('livres', 'date'))
            ->download('inventaire_bibliotheque_'.now()->format('Y-m-d').'.pdf');
    }

    public function retards()
    {
        $retards = Emprunt::with(['user:id,name,prenom,matricule,email,telephone', 'livre:id,titre,auteur'])
            ->enRetard()
            ->orderBy('date_retour_prevue')
            ->get();

        $date = now()->format('d/m/Y');
        $totalAmendes = $retards->sum(fn (Emprunt $emprunt) => $emprunt->calculerPenaliteRetard());
        $devise = Parametres::devise();

        return Pdf::loadView('exports.retards', compact('retards', 'date', 'totalAmendes', 'devise'))
            ->download('rapport_retards_'.now()->format('Y-m-d').'.pdf');
    }

    /** Fiche d'activité d'un usager : accessible au personnel et à l'intéressé. */
    public function etudiant(User $etudiant)
    {
        $this->authorize('view', $etudiant);

        $etudiant->load(['emprunts.livre:id,titre,auteur', 'penalites']);
        $date = now()->format('d/m/Y');
        $devise = Parametres::devise();

        return Pdf::loadView('exports.etudiant', compact('etudiant', 'date', 'devise'))
            ->download('fiche_activite_'.str_replace(' ', '_', $etudiant->name).'.pdf');
    }
}
