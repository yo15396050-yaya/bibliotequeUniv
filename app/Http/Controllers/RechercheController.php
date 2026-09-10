<?php

namespace App\Http\Controllers;

use App\Models\Auteur;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Recherche unifiée : une seule barre interroge le catalogue, les auteurs,
 * les exemplaires (code-barres) et, pour le personnel, les usagers.
 */
class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $terme = trim((string) $request->input('q'));
        $user = Auth::user();

        $resultats = [
            'livres' => collect(),
            'auteurs' => collect(),
            'exemplaires' => collect(),
            'usagers' => collect(),
        ];

        if ($terme !== '') {
            $resultats['livres'] = Livre::with('categorieRef:id,nom')
                ->recherche($terme)
                ->filtres($request->only(['categorie', 'langue', 'type', 'annee', 'niveau', 'disponible', 'numerique', 'emplacement']))
                ->orderBy('titre')
                ->paginate(12, ['*'], 'livres')
                ->withQueryString();

            $resultats['auteurs'] = Auteur::withCount('livres')->recherche($terme)->take(6)->get();

            if ($user->peut('exemplaires.voir')) {
                $resultats['exemplaires'] = Exemplaire::with('livre:id,titre')
                    ->where('code_barre', 'like', "%{$terme}%")
                    ->orWhere('numero_inventaire', 'like', "%{$terme}%")
                    ->take(6)->get();
            }

            if ($user->peut('usagers.voir')) {
                $resultats['usagers'] = User::recherche($terme)->take(6)
                    ->get(['id', 'name', 'prenom', 'matricule', 'email', 'role', 'photo']);
            }
        }

        return view('recherche.index', [
            'terme' => $terme,
            'resultats' => $resultats,
            'categories' => Livre::select('categorie')->distinct()->pluck('categorie')->filter()->sort()->values(),
            'langues' => Livre::select('langue')->distinct()->pluck('langue')->filter()->sort()->values(),
            'types' => Livre::TYPES_DOCUMENT,
        ]);
    }

    /** Suggestions instantanées (barre de recherche de la barre de navigation). */
    public function suggestions(Request $request)
    {
        $terme = trim((string) $request->input('q'));

        if (mb_strlen($terme) < 2) {
            return response()->json(['resultats' => []]);
        }

        $livres = Livre::recherche($terme)->orderBy('titre')->take(8)
            ->get(['id', 'titre', 'auteur', 'categorie', 'exemplaires_disponibles']);

        return response()->json([
            'resultats' => $livres->map(fn (Livre $livre) => [
                'id' => $livre->id,
                'titre' => $livre->titre,
                'auteur' => $livre->auteur,
                'categorie' => $livre->categorie,
                'disponible' => $livre->exemplaires_disponibles > 0,
                'url' => route('livres.show', $livre),
            ]),
        ]);
    }
}
