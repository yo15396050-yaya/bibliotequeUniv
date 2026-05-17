<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use Illuminate\Http\Request;

class AmendeController extends Controller
{
    /**
     * Affiche la liste des amendes impayées
     */
    public function index()
    {
        $amendes = Emprunt::with(['user', 'livre'])
            ->where(function($query) {
                $query->where('statut', 'en retard')
                      ->orWhere(function($q) {
                          $q->where('statut', 'en cours')
                            ->where('date_retour_prevue', '<', now());
                      })
                      ->orWhere('amende', '>', 0);
            })
            ->get();
            
        return view('amendes.index', compact('amendes'));
    }

    /**
     * Marque une amende comme payée
     */
    public function payer($id)
    {
        $emprunt = Emprunt::findOrFail($id);
        
        // On fixe l'amende à 0 dans la base
        $emprunt->update(['amende' => 0]);
        
        return back()->with('success', 'L\'amende a été soldée avec succès.');
    }
}
