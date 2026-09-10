<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExemplaireRequest;
use App\Models\Emplacement;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ExemplaireController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Exemplaire::class);

        $exemplaires = Exemplaire::with(['livre:id,titre,auteur,isbn', 'emplacement.rayon.salle'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sq) use ($request) {
                $sq->where('code_barre', 'like', "%{$request->search}%")
                    ->orWhere('numero_inventaire', 'like', "%{$request->search}%")
                    ->orWhereHas('livre', fn ($l) => $l->where('titre', 'like', "%{$request->search}%")
                        ->orWhere('isbn', 'like', "%{$request->search}%"));
            }))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('etat'), fn ($q) => $q->where('etat', $request->etat))
            ->when($request->filled('livre_id'), fn ($q) => $q->where('livre_id', $request->livre_id))
            ->orderBy('code_barre')
            ->paginate(20)
            ->withQueryString();

        $statistiques = Exemplaire::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')->pluck('total', 'statut');

        return view('exemplaires.index', compact('exemplaires', 'statistiques'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Exemplaire::class);

        return view('exemplaires.create', [
            'livres' => Livre::orderBy('titre')->get(['id', 'titre', 'isbn']),
            'emplacements' => Emplacement::with('rayon.salle.bibliotheque')->get(),
            'livrePreSelectionne' => $request->filled('livre_id') ? Livre::find($request->livre_id) : null,
        ]);
    }

    public function store(StoreExemplaireRequest $request)
    {
        $donnees = $request->validated();
        $livre = Livre::findOrFail($donnees['livre_id']);
        $quantite = max(1, (int) ($donnees['quantite'] ?? 1));

        DB::transaction(function () use ($donnees, $livre, $quantite) {
            for ($i = 0; $i < $quantite; $i++) {
                $exemplaire = Exemplaire::create([
                    'livre_id' => $livre->id,
                    // Un code-barres explicite n'a de sens que pour un exemplaire unique.
                    'code_barre' => ($quantite === 1 && ! empty($donnees['code_barre']))
                        ? $donnees['code_barre']
                        : Exemplaire::genererCodeBarre($livre),
                    'numero_inventaire' => $quantite === 1 ? ($donnees['numero_inventaire'] ?? null) : null,
                    'etat' => $donnees['etat'],
                    'statut' => $donnees['statut'],
                    'emplacement_id' => $donnees['emplacement_id'] ?? null,
                    'date_acquisition' => $donnees['date_acquisition'] ?? null,
                    'prix_achat' => $donnees['prix_achat'] ?? null,
                    'notes' => $donnees['notes'] ?? null,
                ]);

                $this->audit->creation($exemplaire,
                    "Exemplaire {$exemplaire->code_barre} ajouté à « {$livre->titre} »", 'exemplaires');
            }

            $livre->synchroniserCompteurs();
        });

        return redirect()->route('livres.show', $livre)
            ->with('success', $quantite.' exemplaire(s) ajouté(s) à « '.$livre->titre.' ».');
    }

    public function show(Exemplaire $exemplaire)
    {
        $this->authorize('view', $exemplaire);

        $exemplaire->load(['livre', 'emplacement.rayon.salle.bibliotheque',
            'emprunts' => fn ($q) => $q->with('user:id,name,prenom,matricule')->latest()->take(10)]);

        return view('exemplaires.show', compact('exemplaire'));
    }

    public function edit(Exemplaire $exemplaire)
    {
        $this->authorize('update', $exemplaire);

        return view('exemplaires.edit', [
            'exemplaire' => $exemplaire->load('livre:id,titre'),
            'livres' => Livre::orderBy('titre')->get(['id', 'titre', 'isbn']),
            'emplacements' => Emplacement::with('rayon.salle.bibliotheque')->get(),
        ]);
    }

    public function update(StoreExemplaireRequest $request, Exemplaire $exemplaire)
    {
        $this->authorize('update', $exemplaire);

        $donnees = $request->validated();

        // Un exemplaire engagé dans un emprunt en cours reste « emprunté ».
        if ($exemplaire->emprunts()->whereIn('statut', ['en cours', 'en retard'])->exists()
            && $donnees['statut'] !== Exemplaire::STATUT_EMPRUNTE) {
            return back()->withInput()
                ->with('error', 'Cet exemplaire est actuellement emprunté : enregistrez son retour avant de changer son statut.');
        }

        $exemplaire->update(collect($donnees)->except('quantite')->all());
        $exemplaire->livre?->synchroniserCompteurs();

        $this->audit->modification($exemplaire, "Exemplaire {$exemplaire->code_barre} modifié", 'exemplaires');

        return redirect()->route('exemplaires.show', $exemplaire)->with('success', 'Exemplaire mis à jour.');
    }

    public function destroy(Exemplaire $exemplaire)
    {
        $this->authorize('delete', $exemplaire);

        // Règle métier : un exemplaire engagé dans un emprunt reste en base.
        if ($exemplaire->emprunts()->whereIn('statut', ['en cours', 'en retard'])->exists()) {
            return back()->with('error',
                'Cet exemplaire est actuellement emprunté : enregistrez son retour avant de le supprimer.');
        }

        $livre = $exemplaire->livre;
        $code = $exemplaire->code_barre;

        $this->audit->suppression($exemplaire, "Exemplaire {$code} supprimé", 'exemplaires');
        $exemplaire->delete();
        $livre?->synchroniserCompteurs();

        return redirect()->route('livres.show', $livre)->with('success', "Exemplaire {$code} supprimé.");
    }

    /** Étiquette imprimable : code-barres + QR code de l'exemplaire. */
    public function etiquette(Exemplaire $exemplaire)
    {
        $this->authorize('view', $exemplaire);

        $exemplaire->load('livre:id,titre,auteur,isbn');

        $qrCode = 'data:image/svg+xml;base64,'.base64_encode(
            QrCode::size(160)->format('svg')->color(93, 64, 55)
                ->generate(route('livres.show', $exemplaire->livre_id))
        );

        $codeBarre = \App\Support\CodeBarre::dataUri($exemplaire->code_barre);

        return view('exemplaires.etiquette', compact('exemplaire', 'qrCode', 'codeBarre'));
    }

    /** Recherche d'un exemplaire par code-barres (scan). */
    public function rechercheParCode(Request $request)
    {
        $this->authorize('viewAny', Exemplaire::class);

        $donnees = $request->validate(['code_barre' => ['required', 'string', 'max:50']]);

        $exemplaire = Exemplaire::with(['livre:id,titre,auteur,image_couverture', 'emplacement'])
            ->where('code_barre', $donnees['code_barre'])
            ->first();

        if (! $exemplaire) {
            return response()->json([
                'trouve' => false,
                'message' => "Aucun exemplaire ne correspond au code-barres « {$donnees['code_barre']} ».",
            ], 404);
        }

        return response()->json([
            'trouve' => true,
            'exemplaire' => [
                'id' => $exemplaire->id,
                'code_barre' => $exemplaire->code_barre,
                'statut' => $exemplaire->statut,
                'libelle_statut' => $exemplaire->libelle_statut,
                'disponible' => $exemplaire->estDisponible(),
                'livre_id' => $exemplaire->livre_id,
                'titre' => $exemplaire->livre?->titre,
                'auteur' => $exemplaire->livre?->auteur,
                'url' => route('exemplaires.show', $exemplaire),
            ],
        ]);
    }
}
