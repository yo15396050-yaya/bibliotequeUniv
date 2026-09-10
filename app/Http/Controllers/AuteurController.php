<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuteurRequest;
use App\Models\Auteur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuteurController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('can:auteurs.gerer')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $auteurs = Auteur::withCount('livres')
            ->recherche($request->input('search'))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('auteurs.index', compact('auteurs'));
    }

    public function create()
    {
        return view('auteurs.create');
    }

    public function store(StoreAuteurRequest $request)
    {
        $donnees = $request->validated();

        if ($request->hasFile('photo')) {
            $donnees['photo'] = $request->file('photo')->store('auteurs', 'public');
        }

        $auteur = Auteur::create($donnees);
        $this->audit->creation($auteur, "Auteur « {$auteur->nom_complet} » créé", 'catalogue');

        return redirect()->route('auteurs.show', $auteur)->with('success', 'Auteur ajouté.');
    }

    public function show(Auteur $auteur)
    {
        $auteur->load(['livres' => fn ($q) => $q->orderBy('titre')]);

        return view('auteurs.show', compact('auteur'));
    }

    public function edit(Auteur $auteur)
    {
        return view('auteurs.edit', compact('auteur'));
    }

    public function update(StoreAuteurRequest $request, Auteur $auteur)
    {
        $donnees = $request->validated();

        if ($request->hasFile('photo')) {
            if ($auteur->photo) {
                Storage::disk('public')->delete($auteur->photo);
            }
            $donnees['photo'] = $request->file('photo')->store('auteurs', 'public');
        }

        $auteur->update($donnees);
        $this->audit->modification($auteur, "Auteur « {$auteur->nom_complet} » modifié", 'catalogue');

        return redirect()->route('auteurs.show', $auteur)->with('success', 'Auteur mis à jour.');
    }

    public function destroy(Auteur $auteur)
    {
        if ($auteur->livres()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet auteur : des ouvrages lui sont rattachés.');
        }

        if ($auteur->photo) {
            Storage::disk('public')->delete($auteur->photo);
        }

        $this->audit->suppression($auteur, "Auteur « {$auteur->nom_complet} » supprimé", 'catalogue');
        $auteur->delete();

        return redirect()->route('auteurs.index')->with('success', 'Auteur supprimé.');
    }
}
