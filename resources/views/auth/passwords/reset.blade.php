@extends('layouts.public')
@section('title', 'Réinitialiser le mot de passe — BiblioteqUniv')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="carte p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="pastille mx-auto mb-3"><i class="fas fa-key"></i></div>
                    <h2 class="h4 fw-bold mb-1">Nouveau mot de passe</h2>
                    <p class="text-muted small mb-0">8 caractères minimum.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Adresse e-mail</label>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ $email ?? old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Nouveau mot de passe</label>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label small fw-semibold">Confirmation</label>
                        <input id="password-confirm" type="password" name="password_confirmation"
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-marque w-100 py-2">Réinitialiser</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
