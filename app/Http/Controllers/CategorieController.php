<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategorieRequest;
use App\Models\Categorie;
use App\Models\Livre;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('can:categories.gerer')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $categories = Categorie::with(['enfants', 'parent:id,nom'])
            ->withCount('livres')
            ->racines()
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', "%{$request->search}%"))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create', [
            'parents' => Categorie::racines()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(StoreCategorieRequest $request)
    {
        $categorie = Categorie::create($request->validated());
        $this->audit->creation($categorie, "Catégorie « {$categorie->nom} » créée", 'catalogue');

        return redirect()->route('categories.index')->with('success', 'Catégorie ajoutée.');
    }

    public function show(Categorie $categorie)
    {
        $categorie->load(['enfants', 'parent']);

        $livres = Livre::where('categorie_id', $categorie->id)
            ->orWhere('categorie', $categorie->nom)
            ->orderBy('titre')
            ->paginate(15);

        return view('categories.show', compact('categorie', 'livres'));
    }

    public function edit(Categorie $categorie)
    {
        return view('categories.edit', [
            'categorie' => $categorie,
            'parents' => Categorie::racines()->where('id', '!=', $categorie->id)->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function update(StoreCategorieRequest $request, Categorie $categorie)
    {
        $categorie->update($request->validated());
        $this->audit->modification($categorie, "Catégorie « {$categorie->nom} » modifiée", 'catalogue');

        return redirect()->route('categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->enfants()->exists()) {
            return back()->with('error', 'Supprimez d\'abord les sous-catégories rattachées.');
        }

        if ($categorie->livres()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette catégorie : des ouvrages y sont rattachés.');
        }

        $this->audit->suppression($categorie, "Catégorie « {$categorie->nom} » supprimée", 'catalogue');
        $categorie->delete();

        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée.');
    }
}
