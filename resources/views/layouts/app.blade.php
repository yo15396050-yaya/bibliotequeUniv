<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bibliothèque Universitaire')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --wood-dark: #5D4037;    /* Primaire : Bois Sombre */
            --wood-deeper: #3E2723;  /* Pour le footer et les contrastes */
            --accent-gold: #D4AF37;  /* Accents : Or */
            --accent-leather: #A1887F;/* Accents : Cuir */
            --paper-cream: #FAF3E0;  /* Arrière-plan : Papier/Crème */
        }

        body {
            background-color: var(--paper-cream);
            color: #2C1B18;
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3, .navbar-brand, h5 {
            font-family: 'Playfair Display', serif;
        }

        /* Navigation Style Bois */
        .navbar {
            background-color: var(--wood-dark) !important;
            border-bottom: 3px solid var(--accent-gold);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .navbar-brand {
            color: var(--accent-gold) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: #EFEBE9 !important;
            transition: color 0.3s;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--accent-gold) !important;
        }

        /* Styles des Alertes */
        .alert-success {
            background-color: #D4E157;
            border-color: #AFB42B;
            color: #33691E;
        }

        /* Footer Style Bois Sombre */
        footer {
            background-color: var(--wood-deeper) !important;
            border-top: 2px solid var(--accent-gold);
            margin-top: auto;
        }

        .dropdown-menu {
            background-color: #fff;
            border: 1px solid var(--accent-leather);
        }

        .dropdown-item:hover {
            background-color: var(--paper-cream);
            color: var(--wood-dark);
        }

        main {
            flex: 1;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-book me-2"></i>Bibliothèque
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="dropdown" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Accueil</a>
                    </li>
                    
                    @if(Auth::check())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Tableau de bord</a>
                        </li>
                        
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('livres.index') }}">Livres</a>
                            </li>
                        @endif
                    @endif
                </ul>
                
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ Auth::user()->name ?? 'Utilisateur' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile') }}">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Connexion
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <footer class="text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Bibliothèque Universitaire</h5>
                    <p class="mb-0 text-light" style="opacity: 0.7;">© {{ date('Y') }} Tous droits réservés</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0" style="color: var(--accent-gold);">Projet de gestion de bibliothèque</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            $('form').submit(function() {
                $(this).find('button[type="submit"]').prop('disabled', true);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>