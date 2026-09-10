<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Livre;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservations) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);

        $user = Auth::user();

        $reservations = Reservation::with(['user:id,name,prenom,matricule', 'livre:id,titre,auteur', 'exemplaire:id,code_barre'])
            // Un usager ne voit que ses propres réservations.
            ->when(! $user->peut('reservations.voir'), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('livre',
                fn ($l) => $l->where('titre', 'like', "%{$request->search}%")))
            ->orderByDesc('date_reservation')
            ->paginate(20)
            ->withQueryString();

        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $this->authorize('create', Reservation::class);

        $user = Auth::user();

        return view('reservations.create', [
            'livres' => Livre::orderBy('titre')->get(['id', 'titre', 'auteur', 'exemplaires_disponibles']),
            'etudiants' => $user->peut('reservations.gerer')
                ? User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])->actifs()
                    ->orderBy('name')->get(['id', 'name', 'prenom', 'matricule'])
                : collect(),
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        $auteurDemande = Auth::user();
        $donnees = $request->validated();

        // Seul le personnel peut réserver au nom d'un tiers.
        $beneficiaire = $auteurDemande->peut('reservations.gerer') && ! empty($donnees['user_id'])
            ? User::findOrFail($donnees['user_id'])
            : $auteurDemande;

        $reservation = $this->reservations->reserver(
            $beneficiaire,
            Livre::findOrFail($donnees['livre_id']),
            $donnees['notes'] ?? null
        );

        return redirect()->route('reservations.show', $reservation)
            ->with('success', "Réservation enregistrée — position {$reservation->position_file_attente} dans la file d'attente.");
    }

    /** Réservation directe depuis la fiche d'un ouvrage. */
    public function reserver(Livre $livre)
    {
        $this->authorize('create', Reservation::class);

        $reservation = $this->reservations->reserver(Auth::user(), $livre);

        return back()->with('success',
            "Ouvrage réservé — vous êtes en position {$reservation->position_file_attente} dans la file d'attente.");
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $reservation->load(['user', 'livre', 'exemplaire.emplacement']);

        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $reservation->load(['user', 'livre']);

        return view('reservations.edit', [
            'reservation' => $reservation,
            'livres' => Livre::orderBy('titre')->get(['id', 'titre', 'auteur']),
            'etudiants' => User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])
                ->orderBy('name')->get(['id', 'name', 'prenom', 'matricule']),
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $donnees = $request->validate([
            'date_expiration' => ['required', 'date', 'after_or_equal:today'],
            'statut' => ['required', 'in:active,expirée,annulée,honorée'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $reservation->update($donnees);
        $this->reservations->reordonnerFile($reservation->livre);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Réservation mise à jour.');
    }

    public function annuler(Request $request, Reservation $reservation)
    {
        $this->authorize('annuler', $reservation);

        $this->reservations->annuler($reservation, $request->input('motif'));

        return back()->with('success', 'Réservation annulée.');
    }

    /** Met un exemplaire de côté et prévient le premier de la file. */
    public function notifier(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $exemplaire = $reservation->livre?->exemplairesDisponibles()->first();

        if (! $exemplaire) {
            return back()->with('error', 'Aucun exemplaire disponible à mettre de côté pour le moment.');
        }

        $this->reservations->notifierProchainDeLaFile($reservation->livre, $exemplaire);

        return back()->with('success', 'Le premier usager de la file a été notifié.');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $livre = $reservation->livre;

        if ($reservation->statut === Reservation::STATUT_ACTIVE) {
            $this->reservations->annuler($reservation, 'Suppression administrative');
        }

        $reservation->delete();
        $this->reservations->reordonnerFile($livre);

        return redirect()->route('reservations.index')->with('success', 'Réservation supprimée.');
    }
}
