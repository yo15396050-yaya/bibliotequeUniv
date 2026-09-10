@extends('layouts.public')

@section('title', 'Connexion — BiblioteqUniv')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center g-5">

        {{-- Argumentaire --}}
        <div class="col-lg-5 d-none d-lg-block">
            <h1 class="fw-bold mb-3" style="color: var(--bleu-nuit);">
                Bienvenue sur<br>BiblioteqUniv
            </h1>
            <p class="text-muted mb-4">
                Retrouvez le catalogue, vos emprunts, vos réservations et vos documents
                numériques dans un seul espace.
            </p>

            @foreach([
                ['fa-book-open-reader', 'Catalogue en ligne', 'Recherchez par titre, auteur, ISBN ou mot-clé.'],
                ['fa-hand-holding', 'Suivi des emprunts', 'Échéances, renouvellements et historique.'],
                ['fa-bell', 'Notifications', "Rappels d'échéance et réservations disponibles."],
            ] as [$icone, $titre, $texte])
                <div class="d-flex gap-3 mb-3">
                    <div class="pastille flex-shrink-0"><i class="fas {{ $icone }}"></i></div>
                    <div>
                        <div class="fw-semibold">{{ $titre }}</div>
                        <div class="small text-muted">{{ $texte }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Formulaire --}}
        <div class="col-md-8 col-lg-5">
            <div class="carte p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="pastille mx-auto mb-3"><i class="fas fa-book-open"></i></div>
                    <h2 class="h4 fw-bold mb-1">Connectez-vous à votre compte</h2>
                    <p class="text-muted small mb-0">Adresse universitaire ou matricule.</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger border-0 small">
                        <i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success border-0 small">
                        <i class="fas fa-circle-check me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Adresse e-mail</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-envelope text-muted"></i></span>
                            <input id="email" type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus autocomplete="email"
                                   placeholder="prenom.nom@univ.edu">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-lock text-muted"></i></span>
                            <input id="password" type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password" placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button" id="bascule-mot-de-passe"
                                    aria-label="Afficher le mot de passe">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember"
                                   id="remember" @checked(old('remember'))>
                            <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                        </div>
                        @if(Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-marque w-100 py-2">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> Se connecter
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center small text-muted mb-0">
                    Vous n'avez pas de compte ? Les accès sont créés par la bibliothèque —
                    <a href="{{ route('aide') }}" class="text-decoration-none">contactez-nous</a>.
                </p>
            </div>

            <p class="text-center small text-muted mt-3 mb-0">
                <a href="{{ route('conditions') }}" class="text-muted">Conditions d'utilisation</a>
                ·
                <a href="{{ route('faq') }}" class="text-muted">FAQ</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Affichage/masquage du mot de passe.
    const bascule = document.getElementById('bascule-mot-de-passe');
    const champ = document.getElementById('password');

    bascule?.addEventListener('click', function () {
        const visible = champ.type === 'text';
        champ.type = visible ? 'password' : 'text';
        this.querySelector('i').className = visible ? 'fas fa-eye' : 'fas fa-eye-slash';
        this.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
    });
</script>
@endpush
