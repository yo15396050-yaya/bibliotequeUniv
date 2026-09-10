@extends('layouts.public')
@section('title', 'BiblioteqUniv — La bibliothèque universitaire au bout des doigts')

@section('content')
{{-- Bandeau d'accueil --}}
<section style="background: linear-gradient(160deg, var(--bleu-nuit) 0%, var(--bleu-marine) 100%); color:#fff;">
    <div class="container py-5">
        <div class="row align-items-center g-5 py-4">
            <div class="col-lg-6">
                <span class="badge rounded-pill mb-3" style="background: rgba(255,255,255,.15);">
                    <i class="fas fa-graduation-cap me-1"></i> Bibliothèque universitaire
                </span>
                <h1 class="display-5 fw-bold mb-3">La bibliothèque universitaire,<br>au bout des doigts.</h1>
                <p class="lead mb-4" style="opacity:.85;">
                    Accédez, gérez et profitez de toutes les ressources de votre bibliothèque
                    universitaire, simplement.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="btn btn-marque-clair px-4">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> Commencer
                    </a>
                    <a href="{{ route('guide') }}" class="btn btn-outline-light">Découvrir le guide</a>
                </div>

                <div class="d-flex flex-wrap gap-4 mt-5">
                    <div>
                        <div class="fs-3 fw-bold">{{ number_format($statistiques['ouvrages'], 0, ',', ' ') }}</div>
                        <div class="small" style="opacity:.75;">ouvrages au catalogue</div>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ number_format($statistiques['disponibles'], 0, ',', ' ') }}</div>
                        <div class="small" style="opacity:.75;">titres disponibles</div>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ number_format($statistiques['numeriques'], 0, ',', ' ') }}</div>
                        <div class="small" style="opacity:.75;">documents numériques</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="carte p-4 text-dark shadow-lg">
                    <h5 class="mb-3"><i class="fas fa-compass me-2" style="color: var(--bleu-roi);"></i>Votre parcours</h5>
                    <div class="d-flex gap-3 mb-3">
                        <div class="pastille flex-shrink-0"><i class="fas fa-magnifying-glass"></i></div>
                        <div>
                            <div class="fw-semibold">Une bibliothèque plus proche de vous</div>
                            <div class="small text-muted">Trouvez facilement des livres, des ressources et des services, où que vous soyez sur le campus.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="pastille flex-shrink-0"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <div class="fw-semibold">Tout votre univers dans une seule application</div>
                            <div class="small text-muted">Catalogue, emprunts, réservations, pénalités et documents numériques réunis.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="pastille flex-shrink-0"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <div class="fw-semibold">Étudiez, explorez, progressez</div>
                            <div class="small text-muted">La bibliothèque vous accompagne dans votre réussite universitaire.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Fonctionnalités --}}
<section class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Ce que vous pouvez faire</h2>
        <p class="text-muted mb-0">Un service complet, du catalogue à la restitution.</p>
    </div>

    <div class="row g-4">
        @foreach([
            ['fa-book-open-reader', 'Catalogue en ligne', 'Recherchez par titre, auteur, ISBN, mot-clé ou code-barres depuis une seule barre de recherche.'],
            ['fa-hand-holding', 'Emprunts suivis', 'Consultez vos emprunts en cours, vos échéances et demandez un renouvellement en un clic.'],
            ['fa-bookmark', 'Réservations', "Réservez un ouvrage indisponible et soyez prévenu dès qu'un exemplaire revient."],
            ['fa-file-pdf', 'Documents numériques', 'Lisez en ligne les mémoires, thèses et ouvrages numérisés auxquels vous avez accès.'],
            ['fa-bell', 'Notifications', 'Rappels d\'échéance, alertes de retard et disponibilité des réservations.'],
            ['fa-barcode', 'Scan de code-barres', "Au guichet, le scan identifie instantanément l'exemplaire et l'emprunt associé."],
        ] as [$icone, $titre, $texte])
            <div class="col-md-6 col-lg-4">
                <div class="carte h-100 p-4">
                    <div class="pastille mb-3"><i class="fas {{ $icone }}"></i></div>
                    <h5 class="fw-semibold">{{ $titre }}</h5>
                    <p class="text-muted small mb-0">{{ $texte }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- Appel à l'action --}}
<section class="container pb-5">
    <div class="carte p-5 text-center" style="background: var(--bleu-clair); border: none;">
        <h3 class="fw-bold mb-2">Prêt à explorer le catalogue ?</h3>
        <p class="text-muted mb-4">Connectez-vous avec votre matricule ou votre adresse universitaire.</p>
        <a href="{{ route('login') }}" class="btn btn-marque px-5">Se connecter</a>
    </div>
</section>
@endsection
