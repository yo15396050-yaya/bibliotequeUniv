<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpruntRequest;
use App\Http\Requests\StoreRetourRequest;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\User;
use App\Notifications\RetardSignale;
use App\Services\EmpruntService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmpruntController extends Controller
{
    public function __construct(
        private readonly EmpruntService $emprunts,
        private readonly NotificationService $notifications,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Emprunt::class);

        $emprunts = Emprunt::with(['user:id,name,prenom,matricule,email', 'livre:id,titre,auteur', 'exemplaire:id,code_barre'])
            ->when($request->filled('statut'), fn ($q) => $request->statut === 'en retard'
                ? $q->enRetard()
                : $q->where('statut', $request->statut))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sq) use ($request) {
                $sq->whereHas('user', fn ($u) => $u->recherche($request->search))
                    ->orWhereHas('livre', fn ($l) => $l->where('titre', 'like', "%{$request->search}%"))
                    ->orWhereHas('exemplaire', fn ($e) => $e->where('code_barre', $request->search));
            }))
            ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('date_emprunt', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn ($q) => $q->whereDate('date_emprunt', '<=', $request->date_fin))
            ->orderByDesc('date_emprunt')
            ->paginate(20)
            ->withQueryString();

        $statistiques = [
            'total' => Emprunt::count(),
            'en_cours' => Emprunt::where('statut', Emprunt::STATUT_EN_COURS)->count(),
            'en_retard' => Emprunt::enRetard()->count(),
            'retournes' => Emprunt::where('statut', Emprunt::STATUT_RETOURNE)->count(),
        ];

        return view('emprunts.index', compact('emprunts', 'statistiques'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Emprunt::class);

        $utilisateurs = User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])
            ->actifs()->orderBy('name')
            ->get(['id', 'name', 'prenom', 'matricule', 'role', 'filiere']);

        $livres = Livre::disponibles()->orderBy('titre')
            ->get(['id', 'titre', 'auteur', 'isbn', 'exemplaires_disponibles']);

        // Pré-remplissage possible depuis un scan de code-barres ou une fiche livre.
        $exemplairePreSelectionne = $request->filled('code_barre')
            ? Exemplaire::with('livre:id,titre')->where('code_barre', $request->code_barre)->first()
            : null;

        return view('emprunts.create', [
            'utilisateurs' => $utilisateurs,
            'livres' => $livres,
            'livrePreSelectionne' => $request->filled('livre_id') ? Livre::find($request->livre_id) : null,
            'exemplairePreSelectionne' => $exemplairePreSelectionne,
        ]);
    }

    public function store(StoreEmpruntRequest $request)
    {
        $donnees = $request->validated();

        $emprunt = $this->emprunts->enregistrerEmprunt(
            User::findOrFail($donnees['user_id']),
            Livre::findOrFail($donnees['livre_id']),
            isset($donnees['exemplaire_id']) ? Exemplaire::find($donnees['exemplaire_id']) : null,
            isset($donnees['date_retour_prevue']) ? Carbon::parse($donnees['date_retour_prevue']) : null,
            $donnees['notes'] ?? null
        );

        return redirect()->route('emprunts.show', $emprunt)
            ->with('success', 'Emprunt enregistré. Retour prévu le '
                .$emprunt->date_retour_prevue->format('d/m/Y').'.');
    }

    public function show(Emprunt $emprunt)
    {
        $this->authorize('view', $emprunt);

        $emprunt->load(['user', 'livre', 'exemplaire.emplacement', 'bibliothecaire:id,name',
            'receptionniste:id,name', 'renouvellements.demandeur:id,name', 'penalites']);

        return view('emprunts.show', compact('emprunt'));
    }

    public function destroy(Emprunt $emprunt)
    {
        $this->authorize('delete', $emprunt);

        if ($emprunt->estEnCours()) {
            return back()->with('error', 'Un emprunt en cours ne peut pas être supprimé : enregistrez d\'abord le retour.');
        }

        $emprunt->delete();

        return redirect()->route('emprunts.index')->with('success', 'Emprunt supprimé.');
    }

    /** Enregistrement du retour d'un exemplaire. */
    public function retour(StoreRetourRequest $request, Emprunt $emprunt)
    {
        $emprunt = $this->emprunts->enregistrerRetour(
            $emprunt,
            $request->validated('etat_retour'),
            $request->validated('observation')
        );

        $penalite = $emprunt->penalites()->latest()->first();

        return redirect()->route('emprunts.show', $emprunt)
            ->with('success', 'Retour enregistré.'.($penalite && ! $penalite->estSoldee()
                ? ' Pénalité appliquée : '.\App\Support\Parametres::formaterMontant($penalite->montant).'.'
                : ''));
    }

    /**
     * Guichet de retour rapide : recherche l'emprunt à partir du code-barres
     * scanné et le restitue immédiatement.
     */
    public function guichetRetour(Request $request)
    {
        $this->authorize('create', Emprunt::class);

        $emprunt = null;
        $erreur = null;

        if ($code = trim((string) $request->input('code_barre'))) {
            $emprunt = Emprunt::with(['user', 'livre', 'exemplaire'])
                ->enCours()
                ->whereHas('exemplaire', fn ($q) => $q->where('code_barre', $code))
                ->first();

            if (! $emprunt) {
                $erreur = "Aucun emprunt en cours pour le code-barres « {$code} ».";
            }
        }

        return view('emprunts.guichet', compact('emprunt', 'erreur'));
    }

    /** Reçu PDF de l'emprunt. */
    public function genererFiche(Emprunt $emprunt)
    {
        $this->authorize('view', $emprunt);

        $emprunt->load(['user', 'livre', 'exemplaire']);

        return Pdf::loadView('emprunts.fiche', compact('emprunt'))
            ->download("recu-emprunt-{$emprunt->id}.pdf");
    }

    /** Marque les emprunts échus comme « en retard » et génère les pénalités. */
    public function rappelRetard()
    {
        $this->authorize('create', Emprunt::class);

        $resultat = $this->emprunts->traiterRetards();

        return redirect()->route('emprunts.index')->with('success', sprintf(
            '%d emprunt(s) passé(s) en retard, %d pénalité(s) générée(s).',
            $resultat['marques'],
            $resultat['penalites']
        ));
    }

    /** Envoie un rappel individuel à l'usager en retard. */
    public function envoyerRappel(Emprunt $emprunt)
    {
        $this->authorize('retour', $emprunt);

        if (! $emprunt->estEnRetard()) {
            return back()->with('error', "Cet emprunt n'est pas en retard.");
        }

        $this->notifications->envoyer($emprunt->user, new RetardSignale($emprunt));

        return back()->with('success', 'Rappel envoyé à '.$emprunt->user->name.'.');
    }

    /** Espace usager : mes emprunts. */
    public function mesEmprunts(Request $request)
    {
        $emprunts = Emprunt::with(['livre:id,titre,auteur,image_couverture', 'exemplaire:id,code_barre', 'renouvellements'])
            ->where('user_id', Auth::id())
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_emprunt')
            ->paginate(10)
            ->withQueryString();

        return view('emprunts.mes-emprunts', compact('emprunts'));
    }
}
