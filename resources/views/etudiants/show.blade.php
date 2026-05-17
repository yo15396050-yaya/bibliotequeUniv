@extends('layouts.dashboard')

@section('title', 'Profil de l\'Étudiant')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('etudiants.index') }}" style="color: #5D4037;">Étudiants</a></li>
    <li class="breadcrumb-item active">Profil détaillé</li>
@endsection

@section('page-title', 'Profil de l\'Étudiant')

@section('content')
    <style>
        .profile-header {
            background: linear-gradient(135deg, #5D4037 0%, #3e2b25 100%);
            color: #D4AF37;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            border-bottom: 4px solid #D4AF37;
        }

        .info-label {
            color: #8d6e63;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .info-value {
            color: #5D4037;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .profile-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            background-color: white;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background-color: #FAF3E0;
            border: 3px solid #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #5D4037;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .stats-box {
            background-color: #fcfaf2;
            border: 1px solid #eaddca;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .btn-edit-gold {
            background-color: #D4AF37;
            color: #5D4037;
            font-weight: bold;
            border: none;
        }

        .btn-edit-gold:hover {
            background-color: #5D4037;
            color: #FAF3E0;
        }

        .section-title {
            border-left: 4px solid #D4AF37;
            padding-left: 10px;
            margin-bottom: 20px;
            color: #5D4037;
            font-weight: bold;
        }
    </style>

    <div class="container-fluid py-4" style="background-color: #FAF3E0; border-radius: 15px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card profile-card">
                    <div class="profile-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle shadow-sm me-4">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold">{{ $etudiant->name }}</h2>
                                {{-- On utilise isset ou une valeur par défaut pour éviter les erreurs --}}
                                <span class="badge {{ ($etudiant->actif ?? true) ? 'bg-success' : 'bg-danger' }} p-2 px-3">
                                    <i
                                        class="fas {{ ($etudiant->actif ?? true) ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ ($etudiant->actif ?? true) ? 'Compte Actif' : 'Compte Inactif' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end d-none d-md-block">
                            <p class="mb-0 small opacity-75">Membre depuis le</p>
                            {{-- CORRECTION ICI : Ajout du ?-> pour éviter l'erreur sur format() --}}
                            <h5 class="fw-bold">{{ $etudiant->created_at?->format('d M Y') ?? 'Date inconnue' }}</h5>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h5 class="section-title"><i class="fas fa-address-card me-2"></i>Coordonnées</h5>

                                <div class="mb-3">
                                    <div class="info-label">Numéro Matricule</div>
                                    <div class="info-value" style="color: #D4AF37;">{{ $etudiant->matricule ?? 'N/A' }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="info-label">Adresse Email</div>
                                    <div class="info-value">{{ $etudiant->email }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="info-label">Téléphone</div>
                                    <div class="info-value">{{ $etudiant->telephone ?? 'Non renseigné' }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="info-label">Date de Naissance</div>
                                    <div class="info-value">
                                        <i class="fas fa-birthday-cake me-2 text-muted"></i>
                                        {{-- CORRECTION ICI : Conversion en Carbon si c'est une string --}}
                                        {{ $etudiant->date_naissance ? \Carbon\Carbon::parse($etudiant->date_naissance)->format('d/m/Y') : 'Non définie' }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="info-label">Adresse Domicile</div>
                                    <div class="info-value fst-italic text-muted" style="font-size: 0.95rem;">
                                        {{ $etudiant->adresse ?? 'Aucune adresse enregistrée' }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <h5 class="section-title"><i class="fas fa-university me-2"></i>Cursus Académique</h5>

                                <div class="row mb-4">
                                    <div class="col-6">
                                        <div class="info-label">Filière</div>
                                        <div class="info-value">{{ $etudiant->filiere ?? 'Non définie' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Niveau Actuel</div>
                                        <div class="info-value"><span class="badge"
                                                style="background-color: #5D4037;">{{ $etudiant->niveau ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-4">
                                        <div class="stats-box shadow-sm">
                                            <div class="info-label text-center">Total Emprunts</div>
                                            <div class="h3 fw-bold mb-0" style="color: #5D4037;">
                                                {{ $etudiant->emprunts->count() }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="stats-box shadow-sm border-warning">
                                            <div class="info-label text-center">En cours</div>
                                            <div class="h3 fw-bold mb-0 text-warning">
                                                {{ $etudiant->emprunts->where('statut', 'en cours')->count() }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div
                                            class="stats-box shadow-sm {{ $etudiant->emprunts->sum('montant_amende') > 0 ? 'border-danger bg-danger-soft' : '' }}">
                                            <div class="info-label text-center">Amendes</div>
                                            <div
                                                class="h3 fw-bold mb-0 {{ $etudiant->emprunts->sum('montant_amende') > 0 ? 'text-danger' : 'text-muted' }}">
                                                {{ number_format($etudiant->emprunts->sum('montant_amende'), 0, ',', ' ') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="section-title mt-4"><i class="fas fa-history me-2"></i>Derniers Emprunts</h5>
                                <div class="table-responsive rounded border shadow-sm bg-white">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-3">Livre</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($etudiant->emprunts->take(5) as $emprunt)
                                                <tr>
                                                    <td class="ps-3 text-truncate" style="max-width: 150px;">
                                                        <span class="fw-bold">{{ $emprunt->livre->titre }}</span>
                                                    </td>
                                                    <td>{{ $emprunt->date_emprunt->format('d/m/y') }}</td>
                                                    <td>
                                                        @if($emprunt->statut == 'rendu')
                                                            <span class="badge bg-success-soft text-success px-2 py-1">Rendu</span>
                                                        @elseif($emprunt->statut == 'en retard')
                                                            <span class="badge bg-danger-soft text-danger px-2 py-1">Retard</span>
                                                        @else
                                                            <span class="badge bg-gold-soft text-brown px-2 py-1">En cours</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted">Aucun emprunt enregistré
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <style>
                            .bg-success-soft {
                                background-color: rgba(46, 204, 113, 0.1);
                            }

                            .bg-danger-soft {
                                background-color: rgba(231, 76, 60, 0.1);
                            }

                            .bg-gold-soft {
                                background-color: rgba(212, 175, 55, 0.1);
                            }
                        </style>

                        <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                            <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-chevron-left me-2"></i>Retour à la liste
                            </a>
                            <div class="btn-group gap-2">
                                <a href="{{ route('exports.etudiant', $etudiant->id) }}"
                                    class="btn btn-outline-danger px-4">
                                    <i class="fas fa-file-pdf me-2"></i>Exporter Fiche (PDF)
                                </a>
                                <a href="{{ route('etudiants.edit', $etudiant) }}" class="btn btn-edit-gold px-4">
                                    <i class="fas fa-user-edit me-2"></i>Modifier le profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center mt-3 text-muted small">
                    {{-- CORRECTION ICI : Ajout du ?-> pour éviter l'erreur --}}
                    Dernière mise à jour du dossier le : {{ $etudiant->updated_at?->format('d/m/Y à H:i') ?? 'Inconnue' }}
                </p>
            </div>
        </div>
    </div>
@endsection