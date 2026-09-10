<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLivreRequest;
use App\Http\Requests\UpdateLivreRequest;
use App\Models\Auteur;
use App\Models\Categorie;
use App\Models\Editeur;
use App\Models\Emplacement;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LivreController extends Controller
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly DocumentService $documents,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Livre::class);

        $livres = Livre::with('categorieRef:id,nom')
            ->withCount(['exemplaires', 'emprunts'])
            ->recherche($request->input('search'))
            ->filtres($request->only(['categorie', 'statut', 'langue', 'type', 'annee', 'niveau', 'disponible', 'numerique']))
            ->orderBy('titre')
            ->paginate(15)
            ->withQueryString();

        $donneesVue = [
            'livres' => $livres,
            'categories' => Livre::query()->select('categorie')->distinct()->pluck('categorie')->filter()->sort()->values(),
            'types' => Livre::TYPES_DOCUMENT,
        ];

        if ($request->ajax()) {
            return view('livres.partials._table', $donneesVue)->render();
        }

        return view('livres.index', $donneesVue);
    }

    public function create()
    {
        $this->authorize('create', Livre::class);

        return view('livres.create', $this->donneesFormulaire());
    }

    public function store(StoreLivreRequest $request)
    {
        $donnees = $request->validated();

        $livre = DB::transaction(function () use ($request, $donnees) {
            $attributs = $this->preparerAttributs($request, $donnees);
            $attributs['exemplaires_disponibles'] = $attributs['exemplaires_totaux'];
            $attributs['statut'] = 'disponible';

            $livre = Livre::create($attributs);

            if (! empty($donnees['auteurs'])) {
                $livre->auteurs()->sync($donnees['auteurs']);
            }

            // Génération automatique des exemplaires physiques.
            if ($request->boolean('generer_exemplaires', true)) {
                $this->genererExemplaires($livre, (int) $attributs['exemplaires_totaux']);
                $livre->synchroniserCompteurs();
            }

            if ($request->hasFile('fichier_numerique')) {
                $this->documents->ajouter($livre, $request->file('fichier_numerique'), [
                    'visibilite' => $donnees['visibilite_document'] ?? 'authentifie',
                    'autoriser_telechargement' => $request->boolean('autoriser_telechargement', true),
                ]);
            }

            $this->audit->creation($livre, "Ouvrage « {$livre->titre} » ajouté au catalogue", 'catalogue');

            return $livre;
        });

        return redirect()->route('livres.show', $livre)
            ->with('success', 'Ouvrage ajouté au catalogue avec ses exemplaires.');
    }

    public function show(Livre $livre)
    {
        $this->authorize('view', $livre);

        $livre->load([
            'auteurs', 'categorieRef', 'editeurRef', 'emplacement.rayon.salle.bibliotheque',
            'exemplaires' => fn ($q) => $q->with('emplacement')->orderBy('code_barre'),
            'documents',
            'reservationsActives.user:id,name,prenom,matricule',
        ]);

        $emprunts = $livre->emprunts()->with('user:id,name,prenom,matricule')
            ->latest('date_emprunt')->take(10)->get();

        $similaires = Livre::where('categorie', $livre->categorie)
            ->where('id', '!=', $livre->id)
            ->disponibles()
            ->take(4)
            ->get(['id', 'titre', 'auteur', 'image_couverture']);

        return view('livres.show', compact('livre', 'similaires', 'emprunts'));
    }

    public function edit(Livre $livre)
    {
        $this->authorize('update', $livre);

        $livre->load('auteurs');

        return view('livres.edit', array_merge($this->donneesFormulaire(), compact('livre')));
    }

    public function update(UpdateLivreRequest $request, Livre $livre)
    {
        $donnees = $request->validated();

        DB::transaction(function () use ($request, $donnees, $livre) {
            $attributs = $this->preparerAttributs($request, $donnees, $livre);
            $attributs['statut'] = $donnees['statut'];

            $livre->update($attributs);

            if ($request->has('auteurs')) {
                $livre->auteurs()->sync($donnees['auteurs'] ?? []);
            }

            if ($request->hasFile('fichier_numerique')) {
                $this->documents->ajouter($livre, $request->file('fichier_numerique'), [
                    'visibilite' => $donnees['visibilite_document'] ?? 'authentifie',
                    'autoriser_telechargement' => $request->boolean('autoriser_telechargement', true),
                ]);
            }

            $livre->synchroniserCompteurs();
            $this->audit->modification($livre, "Ouvrage « {$livre->titre} » modifié", 'catalogue');
        });

        return redirect()->route('livres.show', $livre)
            ->with('success', 'Ouvrage mis à jour.');
    }

    public function destroy(Livre $livre)
    {
        $this->authorize('delete', $livre);

        if ($livre->emprunts()->enCours()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet ouvrage : des exemplaires sont actuellement empruntés.');
        }

        DB::transaction(function () use ($livre) {
            if ($livre->image_couverture) {
                Storage::disk('public')->delete($livre->image_couverture);
            }

            foreach ($livre->documents as $document) {
                $this->documents->supprimer($document);
            }

            $this->audit->suppression($livre, "Ouvrage « {$livre->titre} » supprimé", 'catalogue');
            $livre->delete();
        });

        return redirect()->route('livres.index')->with('success', 'Ouvrage supprimé.');
    }

    /** Catalogue public (usagers). */
    public function catalogue(Request $request)
    {
        $livres = Livre::with('categorieRef:id,nom')
            ->recherche($request->input('search'))
            ->filtres($request->only(['categorie', 'langue', 'type', 'annee', 'niveau', 'disponible', 'numerique', 'emplacement']))
            ->orderBy('titre')
            ->paginate(20)
            ->withQueryString();

        $donneesVue = [
            'livres' => $livres,
            'categories' => Livre::query()->select('categorie')->distinct()->pluck('categorie')->filter()->sort()->values(),
            'langues' => Livre::query()->select('langue')->distinct()->pluck('langue')->filter()->sort()->values(),
            'types' => Livre::TYPES_DOCUMENT,
        ];

        if ($request->ajax()) {
            return view('livres.partials._catalogue_grid', $donneesVue)->render();
        }

        return view('livres.catalogue', $donneesVue);
    }

    /** QR Code renvoyant vers la fiche de l'ouvrage. */
    public function generateQRCode(Livre $livre)
    {
        $this->authorize('view', $livre);

        $svg = QrCode::size(250)->format('svg')->color(93, 64, 55)
            ->generate(route('livres.show', $livre->id));

        $qrCode = 'data:image/svg+xml;base64,'.base64_encode($svg);

        return view('livres.qrcode', compact('livre', 'qrCode'));
    }

    /** Lecture en ligne du document numérique de référence. */
    public function lire(Livre $livre)
    {
        $this->authorize('view', $livre);

        if (! $livre->estDisponibleNumerique()) {
            return back()->with('error', "Cet ouvrage n'est pas disponible en lecture en ligne.");
        }

        return view('livres.lire', compact('livre'));
    }

    /* ---------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------*/

    private function donneesFormulaire(): array
    {
        return [
            'auteursDisponibles' => Auteur::orderBy('nom')->get(['id', 'nom', 'prenom']),
            'editeurs' => Editeur::orderBy('nom')->get(['id', 'nom']),
            'categoriesRef' => Categorie::with('parent:id,nom')->orderBy('nom')->get(),
            'emplacements' => Emplacement::with('rayon.salle.bibliotheque')->get(),
            'types' => Livre::TYPES_DOCUMENT,
            'niveaux' => Livre::NIVEAUX_ACADEMIQUES,
            'visibilites' => \App\Models\DocumentNumerique::VISIBILITES,
        ];
    }

    /** Normalise les champs du formulaire (fichiers, libellés dénormalisés). */
    private function preparerAttributs(Request $request, array $donnees, ?Livre $livre = null): array
    {
        $attributs = collect($donnees)->except([
            'auteurs', 'image_couverture', 'fichier_numerique',
            'visibilite_document', 'generer_exemplaires', 'statut',
        ])->all();

        if ($request->hasFile('image_couverture')) {
            if ($livre?->image_couverture) {
                Storage::disk('public')->delete($livre->image_couverture);
            }
            $attributs['image_couverture'] = $request->file('image_couverture')->store('livres', 'public');
        }

        // Les libellés texte suivent les références normalisées quand elles existent.
        if (! empty($donnees['categorie_id']) && $categorie = Categorie::find($donnees['categorie_id'])) {
            $attributs['categorie'] = $categorie->nom;
        }

        if (! empty($donnees['editeur_id']) && $editeur = Editeur::find($donnees['editeur_id'])) {
            $attributs['editeur'] = $editeur->nom;
        }

        if (! empty($donnees['auteurs'])) {
            $attributs['auteur'] = Auteur::whereIn('id', $donnees['auteurs'])->get()
                ->map->nom_complet->implode(', ') ?: ($donnees['auteur'] ?? '');
        }

        return $attributs;
    }

    /** Crée les exemplaires physiques manquants pour atteindre la quantité voulue. */
    private function genererExemplaires(Livre $livre, int $quantite): void
    {
        $existants = $livre->exemplaires()->count();

        for ($i = $existants; $i < $quantite; $i++) {
            Exemplaire::create([
                'livre_id' => $livre->id,
                'code_barre' => Exemplaire::genererCodeBarre($livre),
                'etat' => 'bon',
                'statut' => Exemplaire::STATUT_DISPONIBLE,
                'emplacement_id' => $livre->emplacement_id,
                'date_acquisition' => now()->toDateString(),
            ]);
        }
    }
}
