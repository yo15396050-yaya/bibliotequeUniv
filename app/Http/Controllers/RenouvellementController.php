<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Renouvellement;
use App\Services\EmpruntService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RenouvellementController extends Controller
{
    public function __construct(private readonly EmpruntService $emprunts) {}

    /** File des demandes de renouvellement (personnel). */
    public function index(Request $request)
    {
        $this->authorize('emprunts.renouveler');

        $renouvellements = Renouvellement::with(['emprunt.livre:id,titre', 'demandeur:id,name,prenom,matricule', 'traitePar:id,name'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('renouvellements.index', compact('renouvellements'));
    }

    /** Demande (usager) ou renouvellement direct (personnel). */
    public function store(Emprunt $emprunt)
    {
        $this->authorize('renouveler', $emprunt);

        $renouvellement = $this->emprunts->renouveler($emprunt, Auth::user());

        return back()->with('success', $renouvellement->statut === 'accepte'
            ? 'Emprunt renouvelé jusqu\'au '.$renouvellement->nouvelle_echeance->format('d/m/Y').'.'
            : 'Demande de renouvellement envoyée : elle sera étudiée par un bibliothécaire.');
    }

    public function traiter(Request $request, Renouvellement $renouvellement)
    {
        $this->authorize('emprunts.renouveler');

        $donnees = $request->validate([
            'decision' => ['required', 'in:accepter,refuser'],
            'motif_refus' => ['nullable', 'required_if:decision,refuser', 'string', 'max:255'],
        ], [
            'motif_refus.required_if' => 'Merci d\'indiquer le motif du refus.',
        ]);

        $this->emprunts->traiterRenouvellement(
            $renouvellement,
            $donnees['decision'] === 'accepter',
            $donnees['motif_refus'] ?? null
        );

        return back()->with('success', 'Demande de renouvellement traitée.');
    }
}
