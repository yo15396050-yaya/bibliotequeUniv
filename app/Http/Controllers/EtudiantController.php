<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEtudiantRequest;
use App\Http\Requests\UpdateEtudiantRequest;
use App\Models\AnneeAcademique;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion des étudiants : vue métier dédiée sur les comptes de rôle
 * « etudiant ». Les enseignants et le personnel passent par UserController.
 */
class EtudiantController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $etudiants = User::role(User::ROLE_ETUDIANT)
            ->withCount(['emprunts as emprunts_en_cours_count' => fn ($q) => $q->enCours()])
            ->recherche($request->input('search'))
            ->when($request->filled('filiere'), fn ($q) => $q->where('filiere', $request->filiere))
            ->when($request->filled('niveau'), fn ($q) => $q->where('niveau', $request->niveau))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $donneesVue = [
            'etudiants' => $etudiants,
            'filieres' => User::role(User::ROLE_ETUDIANT)->select('filiere')->distinct()
                ->pluck('filiere')->filter()->sort()->values(),
            'niveaux' => User::role(User::ROLE_ETUDIANT)->select('niveau')->distinct()
                ->pluck('niveau')->filter()->sort()->values(),
            'statuts' => User::STATUTS,
        ];

        if ($request->ajax()) {
            return view('etudiants.partials._table', $donneesVue)->render();
        }

        return view('etudiants.index', $donneesVue);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('etudiants.create', [
            'statuts' => User::STATUTS,
            'anneesAcademiques' => AnneeAcademique::orderByDesc('date_debut')->get(),
        ]);
    }

    public function store(StoreEtudiantRequest $request)
    {
        $donnees = $request->validated();
        $donnees['role'] = User::ROLE_ETUDIANT;

        if ($request->hasFile('photo')) {
            $donnees['photo'] = $request->file('photo')->store('etudiants', 'public');
        }

        $donnees['password'] = $donnees['password'] ?? null ?: $donnees['matricule'];
        $donnees['actif'] = true;
        $donnees['email_verified_at'] = now();

        $etudiant = User::create($donnees);
        $this->audit->creation($etudiant, "Étudiant « {$etudiant->name} » enregistré", 'usagers');

        return redirect()->route('etudiants.show', $etudiant)
            ->with('success', "Étudiant enregistré. Mot de passe provisoire : {$etudiant->matricule}");
    }

    public function show(User $etudiant)
    {
        $this->authorize('view', $etudiant);

        $etudiant->load([
            'emprunts' => fn ($q) => $q->with('livre:id,titre,auteur')->latest('date_emprunt'),
            'reservations' => fn ($q) => $q->with('livre:id,titre')->actives(),
            'penalites' => fn ($q) => $q->latest(),
            'anneeAcademique',
        ]);

        return view('etudiants.show', [
            'etudiant' => $etudiant,
            'statistiques' => [
                'emprunts_total' => $etudiant->emprunts->count(),
                'emprunts_en_cours' => $etudiant->emprunts->whereIn('statut', ['en cours', 'en retard'])->count(),
                'emprunts_en_retard' => $etudiant->emprunts->where('statut', 'en retard')->count(),
                'dette' => (float) $etudiant->penalitesBloquantes()->sum('montant')
                    - (float) $etudiant->penalitesBloquantes()->sum('montant_paye'),
            ],
            'motifsBlocage' => $etudiant->motifsBlocageEmprunt(),
        ]);
    }

    public function edit(User $etudiant)
    {
        $this->authorize('update', $etudiant);

        return view('etudiants.edit', [
            'etudiant' => $etudiant,
            'statuts' => User::STATUTS,
            'anneesAcademiques' => AnneeAcademique::orderByDesc('date_debut')->get(),
        ]);
    }

    public function update(UpdateEtudiantRequest $request, User $etudiant)
    {
        $donnees = $request->validated();

        if ($request->hasFile('photo')) {
            if ($etudiant->photo) {
                Storage::disk('public')->delete($etudiant->photo);
            }
            $donnees['photo'] = $request->file('photo')->store('etudiants', 'public');
        }

        if (empty($donnees['password'])) {
            unset($donnees['password']);
        }

        $etudiant->update($donnees);
        $this->audit->modification($etudiant, "Étudiant « {$etudiant->name} » modifié", 'usagers');

        return redirect()->route('etudiants.show', $etudiant)->with('success', 'Étudiant mis à jour.');
    }

    public function destroy(User $etudiant)
    {
        $this->authorize('delete', $etudiant);

        if ($etudiant->emprunts()->enCours()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet étudiant : des emprunts sont en cours.');
        }

        $this->audit->suppression($etudiant, "Étudiant « {$etudiant->name} » supprimé", 'usagers');
        $etudiant->delete();

        return redirect()->route('etudiants.index')->with('success', 'Étudiant supprimé.');
    }
}
