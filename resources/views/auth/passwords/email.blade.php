@extends('layouts.public')
@section('title', 'Mot de passe oublié — BiblioteqUniv')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="carte p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="pastille mx-auto mb-3"><i class="fas fa-envelope"></i></div>
                    <h2 class="h4 fw-bold mb-2">Mot de passe oublié</h2>
                    <p class="text-muted small mb-0">
                        Saisissez votre adresse e-mail : nous vous enverrons un lien de réinitialisation.
                    </p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success border-0 small">
                        <i class="fas fa-circle-check me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label small fw-semibold">Adresse e-mail</label>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-marque w-100 py-2">
                        Envoyer le lien de réinitialisation
                    </button>
                </form>

                <p class="text-center small mt-4 mb-0">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Retour à la connexion
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
