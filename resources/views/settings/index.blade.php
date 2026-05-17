@extends('layouts.app')

@section('title', 'Paramètres')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Paramètres</li>
@endsection

@section('page-title', 'Paramètres du Système')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Menu des Paramètres</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-sliders-h me-2"></i>Général
                    </a>
                    <a href="{{ route('settings.notifications') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a>
                    <a href="{{ route('settings.security') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shield-alt me-2"></i>Sécurité
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-palette me-2"></i>Apparence
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Paramètres Généraux
                    </h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom_bibliotheque" class="form-label">Nom de la Bibliothèque</label>
                                    <input type="text" class="form-control" id="nom_bibliotheque" value="Bibliothèque Universitaire">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duree_emprunt" class="form-label">Durée d'emprunt (jours)</label>
                                    <input type="number" class="form-control" id="duree_emprunt" value="14" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_emprunts" class="form-label">Emprunts maximum par étudiant</label>
                                    <input type="number" class="form-control" id="max_emprunts" value="5" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amende_jour" class="form-label">Amende par jour de retard (€)</label>
                                    <input type="number" step="0.01" class="form-control" id="amende_jour" value="0.50" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email_contact" class="form-label">Email de contact</label>
                            <input type="email" class="form-control" id="email_contact" value="contact@bibliotheque.edu">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone_contact" class="form-label">Téléphone de contact</label>
                            <input type="text" class="form-control" id="telephone_contact" value="+33 1 23 45 67 89">
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse_bibliotheque" class="form-label">Adresse de la bibliothèque</label>
                            <textarea class="form-control" id="adresse_bibliotheque" rows="3">123 Rue de l'Université, 75000 Paris</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notifications_email" checked>
                                <label class="form-check-label" for="notifications_email">
                                    Activer les notifications par email
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_renouvellement" checked>
                                <label class="form-check-label" for="auto_renouvellement">
                                    Autoriser le renouvellement automatique
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="reservations_actives">
                                <label class="form-check-label" for="reservations_actives">
                                    Activer le système de réservations
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-secondary me-2">Réinitialiser</button>
                            <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection