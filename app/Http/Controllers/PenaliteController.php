<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaiementRequest;
use App\Http\Requests\StorePenaliteRequest;
use App\Models\Emprunt;
use App\Models\Penalite;
use App\Models\User;
use App\Services\PenaliteService;
use App\Support\Parametres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenaliteController extends Controller
{
    public function __construct(private readonly PenaliteService $penalites) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Penalite::class);

        $user = Auth::user();

        $penalites = Penalite::with(['user:id,name,prenom,matricule,email', 'emprunt.livre:id,titre'])
            // Un usager ne consulte que ses propres pénalités.
            ->when(! $user->peut('penalites.voir'), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('user',
                fn ($u) => $u->recherche($request->search)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $base = Penalite::query()->when(! $user->peut('penalites.voir'), fn ($q) => $q->where('user_id', $user->id));

        $statistiques = [
            'total' => (float) (clone $base)->where('statut', '!=', Penalite::STATUT_ANNULEE)->sum('montant'),
            'encaisse' => (float) (clone $base)->sum('montant_paye'),
            'impaye' => (float) (clone $base)->bloquantes()->sum('montant')
                - (float) (clone $base)->bloquantes()->sum('montant_paye'),
            'nombre_impayees' => (clone $base)->bloquantes()->count(),
        ];

        return view('penalites.index', compact('penalites', 'statistiques'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Penalite::class);

        return view('penalites.create', [
            'usagers' => User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])
                ->orderBy('name')->get(['id', 'name', 'prenom', 'matricule']),
            'empruntPreSelectionne' => $request->filled('emprunt_id')
                ? Emprunt::with('livre:id,titre')->find($request->emprunt_id)
                : null,
            'montantsParDefaut' => [
                Penalite::TYPE_PERTE => Parametres::decimal('penalite.montant_perte'),
                Penalite::TYPE_DEGRADATION => Parametres::decimal('penalite.montant_degradation'),
            ],
        ]);
    }

    public function store(StorePenaliteRequest $request)
    {
        $donnees = $request->validated();

        $penalite = $this->penalites->creerPenalite(
            User::findOrFail($donnees['user_id']),
            $donnees['type'],
            isset($donnees['montant']) ? (float) $donnees['montant'] : null,
            $donnees['motif'],
            isset($donnees['emprunt_id']) ? Emprunt::find($donnees['emprunt_id']) : null
        );

        return redirect()->route('penalites.show', $penalite)
            ->with('success', 'Pénalité enregistrée.');
    }

    public function show(Penalite $penalite)
    {
        $this->authorize('view', $penalite);

        $penalite->load(['user', 'emprunt.livre', 'paiements.caissier:id,name', 'createur:id,name']);

        return view('penalites.show', compact('penalite'));
    }

    /** Encaisse un paiement (total ou partiel). */
    public function payer(StorePaiementRequest $request, Penalite $penalite)
    {
        $donnees = $request->validated();

        $this->penalites->enregistrerPaiement(
            $penalite,
            (float) $donnees['montant'],
            $donnees['mode_paiement'],
            $donnees['reference'] ?? null,
            $donnees['observation'] ?? null
        );

        $penalite->refresh();

        return back()->with('success', $penalite->statut === Penalite::STATUT_PAYEE
            ? 'Pénalité soldée intégralement.'
            : 'Paiement partiel enregistré. Reste à payer : '.Parametres::formaterMontant($penalite->reste_a_payer).'.');
    }

    public function annuler(Request $request, Penalite $penalite)
    {
        $this->authorize('annuler', $penalite);

        $donnees = $request->validate([
            'motif' => ['required', 'string', 'max:255'],
        ]);

        $this->penalites->annuler($penalite, $donnees['motif']);

        return back()->with('success', 'Pénalité annulée.');
    }

    /** Reçu de paiement au format PDF. */
    public function recu(Penalite $penalite)
    {
        $this->authorize('view', $penalite);

        $penalite->load(['user', 'emprunt.livre', 'paiements.caissier:id,name']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('penalites.recu', compact('penalite'))
            ->download("recu-penalite-{$penalite->id}.pdf");
    }
}
