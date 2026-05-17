@extends('layouts.dashboard')

@section('title', 'Modifier la Réservation')

@section('page-title', 'Modifier la Réservation')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Modifier la Réservation #{{ $reservation->id }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reservations.update', $reservation) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="livre_id" class="form-label">Livre *</label>
                                <select name="livre_id" id="livre_id" class="form-select" required>
                                    <option value="">Sélectionner un livre</option>
                                    @foreach($livres as $livre)
                                        <option value="{{ $livre->id }}" {{ old('livre_id', $reservation->livre_id) == $livre->id ? 'selected' : '' }}>
                                            {{ $livre->titre }} - {{ $livre->auteur }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('livre_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">Étudiant *</label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">Sélectionner un étudiant</option>
                                    @foreach($etudiants as $etudiant)
                                        <option value="{{ $etudiant->id }}" {{ old('user_id', $reservation->user_id) == $etudiant->id ? 'selected' : '' }}>
                                            {{ $etudiant->name }} ({{ $etudiant->matricule }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_reservation" class="form-label">Date de réservation *</label>
                                <input type="datetime-local" name="date_reservation" id="date_reservation" 
                                       class="form-control" value="{{ old('date_reservation', $reservation->date_reservation ? $reservation->date_reservation->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                                @error('date_reservation')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_fin_reservation" class="form-label">Date de fin de réservation *</label>
                                <input type="datetime-local" name="date_fin_reservation" id="date_fin_reservation" 
                                       class="form-control" value="{{ old('date_fin_reservation', $reservation->date_fin_reservation ? $reservation->date_fin_reservation->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
                                @error('date_fin_reservation')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $reservation->notes) }}</textarea>
                            <div class="form-text">Informations complémentaires sur la réservation</div>
                            @error('notes')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Statut actuel :</strong> 
                            @if($reservation->estActive())
                                <span class="badge bg-warning text-dark">Active</span>
                            @elseif($reservation->estExpiree())
                                <span class="badge bg-danger">Expirée</span>
                            @else
                                <span class="badge bg-success">Terminée</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
