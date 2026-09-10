@extends('layouts.app')

@section('title', 'Connexion - Bibliothèque Universitaire')

@section('content')
<div class="login-page-container">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="col-md-8 col-lg-5 col-xl-4">
                {{-- Carte de connexion --}}
                <div class="card shadow-lg border-0 overflow-hidden login-card">
                    {{-- Header Premium --}}
                    <div class="card-header text-white text-center py-5 border-0 position-relative" style="background-color: #123A7A;">
                        <div class="header-pattern"></div>
                        <div class="position-relative z-index-2">
                            <div class="mb-3">
                                <i class="fas fa-book-reader fa-3x text-gold shadow-icon"></i>
                            </div>
                            <h3 class="mb-0 text-uppercase tracking-widest fw-bold">
                                Bibliothèque
                            </h3>
                            <div class="text-gold-light small fw-light">UNIVERSITAIRE</div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-md-5 bg-paper">
                        {{-- Gestion des Erreurs --}}
                        @if($errors->any())
                            <div class="alert alert-custom-danger border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <span class="small fw-bold">Identifiants incorrects.</span>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf
                            
                            {{-- Email --}}
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-brown text-uppercase tracking-wider">Email Professionnel</label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="admin@univ-edu.com"
                                           required 
                                           autocomplete="email" 
                                           autofocus>
                                </div>
                            </div>
                            
                            {{-- Password --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-brown text-uppercase tracking-wider mb-0">Mot de passe</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-gold small text-decoration-none fw-bold">Perdu ?</a>
                                    @endif
                                </div>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" 
                                           id="password"
                                           class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                                           name="password" 
                                           placeholder="••••••••"
                                           required>
                                    <button class="btn btn-white border border-start-0 text-muted px-3" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Remember Me --}}
                            <div class="mb-4 form-check custom-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small cursor-pointer" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                            
                            {{-- Submit Button --}}
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-brown btn-lg shadow-sm py-3" id="loginButton">
                                    <span id="buttonText">Connexion</span>
                                    <span id="buttonLoading" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Vérification...
                                    </span>
                                </button>
                            </div>

                            @if(Route::has('register'))
                                <div class="text-center">
                                    <span class="small text-muted">Pas d'accès ? </span>
                                    <a href="{{ route('register') }}" class="small text-brown fw-bold text-decoration-none">S'enregistrer</a>
                                </div>
                            @endif
                        </form>
                    </div>
                    
                    <div class="card-footer bg-white border-0 text-center pb-4 pt-0">
                        <hr class="mx-5 opacity-10">
                        <p class="text-muted" style="font-size: 0.75rem;">
                            &copy; {{ date('Y') }} — BU Management System
                        </p>
                    </div>
                </div>

                {{-- Mock Credentials for easier testing --}}
                @if(config('app.debug'))
                <div class="text-center mt-4">
                    <div class="d-inline-block p-2 px-3 rounded-pill bg-white border shadow-sm small text-muted">
                        <i class="fas fa-info-circle text-gold me-1"></i> <strong>admin@univ.fr</strong> / <strong>password</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --brown: #123A7A;
        --gold: #2563EB;
        --gold-light: #E0C097;
        --paper: #F4F7FC;
        --leather: #64748B;
    }

    .login-page-container {
        background-color: #f8f9fa;
        background-image: 
            radial-gradient(var(--gold-light) 0.5px, transparent 0.5px),
            radial-gradient(var(--leather) 0.5px, #f8f9fa 0.5px);
        background-size: 40px 40px;
        background-position: 0 0, 20px 20px;
        min-height: 100vh;
    }

    .bg-paper { background-color: var(--paper); }
    .text-brown { color: var(--brown); }
    .text-gold { color: var(--gold); }
    .text-gold-light { color: var(--gold-light); }
    
    .login-card {
        border-radius: 1.5rem;
    }

    .btn-brown {
        background-color: var(--brown);
        color: white;
        border: none;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .btn-brown:hover {
        background-color: #0F2557;
        color: var(--gold);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 37, 87, 0.3);
    }

    .header-pattern {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0.08;
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
    }

    .custom-input-group .form-control {
        padding: 0.75rem;
        border-color: #dee2e6;
    }

    .custom-input-group .form-control:focus {
        border-color: var(--gold);
        box-shadow: none;
    }

    .custom-check .form-check-input:checked {
        background-color: var(--gold);
        border-color: var(--gold);
    }

    .alert-custom-danger {
        background-color: #fff5f5;
        border-left: 5px solid #dc3545;
        color: #dc3545;
    }

    .shadow-icon {
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
    }

    .cursor-pointer { cursor: pointer; }
    .tracking-widest { letter-spacing: 0.2em; }
    .z-index-2 { z-index: 2; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
    
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            document.getElementById('buttonText').classList.add('d-none');
            document.getElementById('buttonLoading').classList.remove('d-none');
            document.getElementById('loginButton').disabled = true;
        });
    }
});
</script>
@endpush
@endsection