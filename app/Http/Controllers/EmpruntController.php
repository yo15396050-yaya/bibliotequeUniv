<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Ajout de l'import manquant
use Barryvdh\DomPDF\Facade\Pdf;

class EmpruntController extends Controller
{
    public function index(Request $request)
    {
        $query = Emprunt::with(['user', 'livre']);

        // CORRECTION : Utilisation de filled() au lieu de has()
        // Cela évite de filtrer si le champ est présent mais vide (ex: date_debut=)

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->where('date_emprunt', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_emprunt', '<=', $request->date_fin);
        }

        $emprunts = $query->orderBy('date_emprunt', 'desc')->paginate(20);

        $statistiques = [
            'total' => Emprunt::count(),
            'en_cours' => Emprunt::where('statut', 'en cours')->count(),
            'en_retard' => Emprunt::where('statut', 'en retard')->count(),
            'retournes' => Emprunt::where('statut', 'retourné')->count(),
        ];

        return view('emprunts.index', compact('emprunts', 'statistiques'));
    }

    // ... (create, store, show restent identiques)
    public function create()
    {
        // Récupérer les utilisateurs qui peuvent emprunter (étudiants actifs)
        $utilisateurs = User::where('role', 'etudiant')
            ->where('actif', true)
            ->orderBy('name')
            ->get();

        // Récupérer les livres disponibles (stock > 0)
        $livres = Livre::where('exemplaires_disponibles', '>', 0)
            ->orderBy('titre')
            ->get();

        return view('emprunts.create', compact('utilisateurs', 'livres'));
    }
    public function store(Request $request)
    {
        // 1. Validation des données
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'livre_id' => 'required|exists:livres,id',
            'date_retour_prevue' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::find($validated['user_id']);
        $livre = Livre::find($validated['livre_id']);

        // 2. Vérification de la disponibilité (Optionnel mais recommandé)
        if ($livre->exemplaires_disponibles <= 0) {
            return redirect()->back()->with('error', 'Ce livre n\'est plus disponible.');
        }

        // 3. Enregistrement sécurisé par transaction
        DB::beginTransaction();
        try {
            $emprunt = Emprunt::create([
                'user_id' => $validated['user_id'],
                'livre_id' => $validated['livre_id'],
                'date_emprunt' => now(),
                'date_retour_prevue' => $validated['date_retour_prevue'],
                'notes' => $validated['notes'] ?? null,
                'statut' => 'en cours',
            ]);

            // Mise à jour du stock du livre
            $livre->decrement('exemplaires_disponibles');

            // Mise à jour du compteur de l'étudiant
            $user->increment('nombre_emprunts');

            DB::commit();

            return redirect()->route('emprunts.show', $emprunt)
                ->with('success', 'L\'emprunt a été enregistré avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

    }
    /**
     * Affiche les détails d'un emprunt spécifique.
     */
    public function show(Emprunt $emprunt)
    {
        // On charge les relations pour éviter les requêtes supplémentaires dans la vue
        $emprunt->load(['user', 'livre']);

        // C'est ici que ton design "Bois & Or" va s'afficher
        return view('emprunts.show', compact('emprunt'));
    }
    /**
     * Génère un reçu PDF pour un emprunt spécifique.
     */
    public function genererFiche(Emprunt $emprunt)
    {
        $emprunt->load(['user', 'livre']);

        // On utilise la vue 'emprunts.fiche' pour le design du PDF
        $pdf = Pdf::loadView('emprunts.fiche', compact('emprunt'));

        // Téléchargement du fichier avec un nom explicite
        return $pdf->download("recu-emprunt-{$emprunt->id}.pdf");
    }

    /**
     * Marque automatiquement les emprunts non rendus comme "en retard".
     */
    public function rappelRetard()
    {
        $empruntsEnRetard = Emprunt::where('statut', 'en cours')
            ->where('date_retour_prevue', '<', now())
            ->with(['user', 'livre'])
            ->get();

        foreach ($empruntsEnRetard as $emprunt) {
            $emprunt->update(['statut' => 'en retard']);
        }

        return redirect()->route('emprunts.index')
            ->with('success', count($empruntsEnRetard) . ' emprunt(s) mis à jour en retard.');
    }

    public function retour(Emprunt $emprunt)
    {
        if ($emprunt->statut !== 'en cours' && $emprunt->statut !== 'en retard') {
            return redirect()->back()
                ->with('error', 'Cet emprunt a déjà été retourné.');
        }

        DB::beginTransaction();
        try {
            $amende = 0;
            // Comparaison de dates propre
            if (now()->greaterThan($emprunt->date_retour_prevue)) {
                $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
                $amende = $joursRetard * 100; // 100 FCFA par jour de retard
                $emprunt->statut = 'en retard';
            } else {
                $emprunt->statut = 'retourné';
            }

            $emprunt->date_retour_effective = now();
            $emprunt->amende = $amende;
            $emprunt->save();

            $livre = $emprunt->livre;
            $livre->increment('exemplaires_disponibles');

            if ($livre->exemplaires_disponibles > 0 && $livre->statut == 'emprunté') {
                $livre->update(['statut' => 'disponible']);
            }

            DB::commit();

            $message = 'Retour enregistré avec succès.' . ($amende > 0 ? " Amende : {$amende} FCFA" : '');
            return redirect()->route('emprunts.show', $emprunt)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function mesEmprunts()
    {
        // Auth::id() nécessite l'import "use Illuminate\Support\Facades\Auth;" en haut
        $emprunts = Emprunt::where('user_id', Auth::id())
            ->with(['livre'])
            ->orderBy('date_emprunt', 'desc')
            ->paginate(10);

        return view('emprunts.mes-emprunts', compact('emprunts'));
    }

    // ... (reste du code)
}