<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        $query = Reservation::with(['user', 'livre']);

        if (auth()->user()->estEtudiant()) {
            $query->where('user_id', auth()->id());
        }

        $reservations = $query->orderBy('date_reservation', 'desc')
                             ->paginate(20);
        
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $livres = Livre::where('statut', 'disponible')->get();
        $etudiants = User::where('role', 'etudiant')->where('actif', true)->get();
        
        return view('reservations.create', compact('livres', 'etudiants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'livre_id' => 'required|exists:livres,id',
        ]);

        $livre = Livre::findOrFail($request->livre_id);
        
        // Vérifier si le livre est déjà réservé par cet utilisateur
        $existingReservation = Reservation::where('user_id', $request->user_id)
                                         ->where('livre_id', $request->livre_id)
                                         ->where('statut', 'active')
                                         ->first();

        if ($existingReservation) {
            return back()->with('error', 'Vous avez déjà réservé ce livre.');
        }

        // Calculer la position dans la file d'attente
        $position = Reservation::where('livre_id', $request->livre_id)
                              ->where('statut', 'active')
                              ->count() + 1;

        // Créer la réservation et décrémenter les exemplaires disponibles
        DB::beginTransaction();
        try {
            Reservation::create([
                'user_id' => $request->user_id,
                'livre_id' => $request->livre_id,
                'date_reservation' => now(),
                'date_expiration' => now()->addDays(7),
                'statut' => 'active',
                'position_file_attente' => $position,
            ]);

            // Décrémenter les exemplaires disponibles
            $livre->decrement('exemplaires_disponibles');
            
            // Mettre à jour le statut du livre si nécessaire
            if ($livre->exemplaires_disponibles == 0) {
                $livre->update(['statut' => 'réservé']);
            }

            DB::commit();

            return redirect()->route('reservations.index')
                           ->with('success', 'Réservation créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Une erreur est survenue lors de la réservation.');
        }
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'livre']);
        return view('reservations.show', compact('reservation'));
    }

    public function reserver(Livre $livre)
    {
        $user = Auth::user();
        
        if (!$user->estEtudiant()) {
            return back()->with('error', 'Seuls les étudiants peuvent réserver des livres.');
        }

        if (!$livre->estDisponible()) {
            return back()->with('error', 'Ce livre n\'est pas disponible pour réservation.');
        }

        // Vérifier si l'utilisateur a déjà une réservation active pour ce livre
        $existingReservation = Reservation::where('user_id', $user->id)
                                         ->where('livre_id', $livre->id)
                                         ->where('statut', 'active')
                                         ->first();

        if ($existingReservation) {
            return back()->with('error', 'Vous avez déjà réservé ce livre.');
        }

        // Calculer la position dans la file d'attente
        $position = Reservation::where('livre_id', $livre->id)
                              ->where('statut', 'active')
                              ->count() + 1;

        // Créer la réservation et décrémenter les exemplaires disponibles
        DB::beginTransaction();
        try {
            Reservation::create([
                'user_id' => $user->id,
                'livre_id' => $livre->id,
                'date_reservation' => now(),
                'date_expiration' => now()->addDays(7),
                'statut' => 'active',
                'position_file_attente' => $position,
            ]);

            // Décrémenter les exemplaires disponibles
            $livre->decrement('exemplaires_disponibles');
            
            // Mettre à jour le statut du livre si nécessaire
            if ($livre->exemplaires_disponibles == 0) {
                $livre->update(['statut' => 'réservé']);
            }

            DB::commit();

            return back()->with('success', 'Livre réservé avec succès. Position dans la file: ' . $position);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Une erreur est survenue lors de la réservation.');
        }
    }

    public function annuler(Reservation $reservation)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est le propriétaire ou un admin
        if ($reservation->user_id !== $user->id && !$user->estAdministrateur()) {
            return back()->with('error', 'Action non autorisée.');
        }

        DB::beginTransaction();
        try {
            // Marquer la réservation comme annulée
            $reservation->update(['statut' => 'annulée']);

            // Incrémenter les exemplaires disponibles
            $livre = $reservation->livre;
            $livre->increment('exemplaires_disponibles');
            
            // Mettre à jour le statut du livre si nécessaire
            if ($livre->exemplaires_disponibles > 0 && $livre->statut == 'réservé') {
                $livre->update(['statut' => 'disponible']);
            }

            DB::commit();
            
            return back()->with('success', 'Réservation annulée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Une erreur est survenue lors de l\'annulation.');
        }
    }

    public function edit(Reservation $reservation)
    {
        $livres = Livre::where('statut', 'disponible')->get();
        $etudiants = User::where('role', 'etudiant')->where('actif', true)->get();
        
        return view('reservations.edit', compact('reservation', 'livres', 'etudiants'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'livre_id' => 'required|exists:livres,id',
            'date_reservation' => 'required|date',
            'date_fin_reservation' => 'required|date|after_or_equal:date_reservation',
            'notes' => 'nullable|string|max:500',
        ]);

        $reservation->update($request->all());
        
        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Réservation mise à jour avec succès.');
    }

    public function destroy(Reservation $reservation)
    {
        DB::beginTransaction();
        try {
            // Incrémenter les exemplaires disponibles avant suppression
            $livre = $reservation->livre;
            $livre->increment('exemplaires_disponibles');
            
            // Mettre à jour le statut du livre si nécessaire
            if ($livre->exemplaires_disponibles > 0 && $livre->statut == 'réservé') {
                $livre->update(['statut' => 'disponible']);
            }

            // Supprimer la réservation
            $reservation->delete();
            
            DB::commit();
        
            return redirect()->route('reservations.index')
                           ->with('success', 'Réservation supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}
