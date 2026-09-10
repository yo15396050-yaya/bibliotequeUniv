<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEditeurRequest;
use App\Models\Editeur;
use App\Services\AuditService;
use Illuminate\Http\Request;

class EditeurController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('can:editeurs.gerer')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $editeurs = Editeur::withCount('livres')
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', "%{$request->search}%"))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('editeurs.index', compact('editeurs'));
    }

    public function create()
    {
        return view('editeurs.create');
    }

    public function store(StoreEditeurRequest $request)
    {
        $editeur = Editeur::create($request->validated());
        $this->audit->creation($editeur, "Éditeur « {$editeur->nom} » créé", 'catalogue');

        return redirect()->route('editeurs.index')->with('success', 'Éditeur ajouté.');
    }

    public function show(Editeur $editeur)
    {
        $editeur->load(['livres' => fn ($q) => $q->orderBy('titre')]);

        return view('editeurs.show', compact('editeur'));
    }

    public function edit(Editeur $editeur)
    {
        return view('editeurs.edit', compact('editeur'));
    }

    public function update(StoreEditeurRequest $request, Editeur $editeur)
    {
        $editeur->update($request->validated());
        $this->audit->modification($editeur, "Éditeur « {$editeur->nom} » modifié", 'catalogue');

        return redirect()->route('editeurs.index')->with('success', 'Éditeur mis à jour.');
    }

    public function destroy(Editeur $editeur)
    {
        if ($editeur->livres()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet éditeur : des ouvrages lui sont rattachés.');
        }

        $this->audit->suppression($editeur, "Éditeur « {$editeur->nom} » supprimé", 'catalogue');
        $editeur->delete();

        return redirect()->route('editeurs.index')->with('success', 'Éditeur supprimé.');
    }
}
