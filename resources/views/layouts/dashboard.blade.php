<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de Bord - Bibliothèque Universitaire')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --wood-primary: #5D4037;   /* Bois Sombre */
            --accent-gold: #D4AF37;    /* Or */
            --accent-leather: #A1887F; /* Cuir */
            --paper-bg: #FAF3E0;      /* Papier / Crème */
            --text-main: #2c1b18;
            --card-bg: #ffffff;
            --navbar-bg: #3E2723;
        }

        /* --- MODE SOMBRE (Bois de Minuit) --- */
        [data-theme="dark"] {
            --paper-bg: #121212;      /* Noir Minuit */
            --wood-primary: #1A1210;   /* Bois Très Sombre */
            --accent-gold: #D4AF37;    /* Or (gardé) */
            --accent-leather: #8D6E63;
            --text-main: #E0D5BA;      /* Or Antique / Crème Clair */
            --card-bg: #1E1E1E;        /* Gris Anthracite */
            --navbar-bg: #0D0D0D;      /* Noir Total */
        }

        body {
            background-color: var(--paper-bg);
            font-family: 'Roboto', sans-serif;
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }

        h1, h2, h3, .navbar-brand, .text-uppercase, .app-brand-title, .sidebar-project-name {
            font-family: 'Playfair Display', serif;
        }

        /* --- STYLE SIDEBAR  --- */
        .sidebar-brand-container {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            margin-bottom: 10px;
        }
        .sidebar-project-name {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            margin-left: 10px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .sidebar.collapsed .sidebar-project-name { display: none; }

        /* STYLE DU TITRE DANS L'ANGLE GAUCHE NAVBAR */
        .app-brand-title {
            color: var(--accent-gold);
            font-size: 1.4rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            font-weight: 700;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: var(--wood-primary);
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        
        .sidebar.collapsed { width: 70px; }
        .sidebar.collapsed .sidebar-link span, .sidebar.collapsed .text-uppercase { display: none; }
        .sidebar.collapsed .sidebar-link i { margin-right: 0; text-align: center; width: 100%; }
        
        .main-content {
            margin-left: 250px;
            padding-top: 60px;
            min-height: 100vh;
            transition: all 0.3s;
            background-color: var(--paper-bg);
        }
        
        .card {
            background-color: var(--card-bg);
            color: var(--text-main);
        }
        
        .table {
            color: var(--text-main);
        }
        
        .main-content.expanded { margin-left: 70px; }
        
        .navbar-dashboard {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 999;
            transition: all 0.3s;
            background-color: var(--navbar-bg) !important;
            border-bottom: 2px solid var(--accent-gold);
            height: 70px;
            display: flex;
            align-items: center;
        }
        
        .navbar-dashboard.expanded { left: 70px; }
        
        .sidebar-link {
            display: block;
            padding: 12px 20px;
            color: #d7ccc8;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--accent-gold);
            border-left-color: var(--accent-leather);
        }
        
        .sidebar-link.active {
            background-color: var(--accent-gold);
            color: #3E2723 !important;
            border-left-color: #fff;
            font-weight: bold;
        }
        
        .sidebar-link i { width: 20px; margin-right: 10px; }
        
        .sidebar-toggle {
            position: absolute;
            top: 15px;
            right: 10px;
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
        }

        .profile-container {
            display: flex;
            align-items: center;
            padding: 5px 15px;
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .profile-info { text-align: right; margin-right: 12px; line-height: 1.2; }
        .profile-name { color: #fff; font-weight: 600; font-size: 0.95rem; display: block; }
        .profile-role { color: var(--accent-gold); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
        .profile-avatar-img {
            width: 42px; height: 42px; border-radius: 50%; border: 2px solid var(--accent-gold);
            padding: 2px; object-fit: cover; background-color: var(--wood-primary);
        }

        .dropdown-menu-luxe {
            background-color: #fff; border: none; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-top: 15px !important;
            min-width: 220px; overflow: hidden; padding: 0;
        }
        .dropdown-header-luxe { background-color: var(--wood-primary); padding: 15px; color: #fff; }
        .dropdown-item-luxe { padding: 12px 20px; font-size: 0.9rem; color: var(--wood-primary); display: flex; align-items: center; transition: all 0.2s; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content, .navbar-dashboard { margin-left: 0; left: 0; }
            .profile-info, .app-brand-title { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand-container">
            <i class="fas fa-book text-warning"></i>
            <span class="sidebar-project-name">BibliotequeUniv</span>
        </div>

        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mt-2">
            <h6 class="text-uppercase text-muted px-3 mb-3" style="color: var(--accent-leather) !important; font-size: 0.7rem; letter-spacing: 1px;">Menu Principal</h6>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> <span>{{ Auth::user()->estEtudiant() ? 'Mon Espace' : 'Tableau de bord' }}</span>
            </a>

            @if(Auth::user()->estAdministrateur() || Auth::user()->estBibliothecaire())
                <a href="{{ route('livres.index') }}" class="sidebar-link {{ request()->is('livres*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i> <span>Livres</span>
                </a>
                <a href="{{ route('etudiants.index') }}" class="sidebar-link {{ request()->is('etudiants*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i> <span>Étudiants</span>
                </a>
                <a href="{{ route('emprunts.index') }}" class="sidebar-link {{ request()->is('emprunts*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding"></i> <span>Emprunts</span>
                </a>
                <a href="{{ route('reservations.index') }}" class="sidebar-link {{ request()->is('reservations*') ? 'active' : '' }}">
                    <i class="fas fa-bookmark"></i> <span>Réservations</span>
                </a>
            @endif

            @if(Auth::user()->estEtudiant())
                <a href="{{ route('mes-emprunts') }}" class="sidebar-link {{ request()->is('mes-emprunts') ? 'active' : '' }}">
                    <i class="fas fa-history"></i> <span>Mes Emprunts</span>
                </a>
                <a href="{{ route('reservations.index') }}" class="sidebar-link {{ request()->is('reservations*') ? 'active' : '' }}">
                    <i class="fas fa-bookmark"></i> <span>Mes Réservations</span>
                </a>
            @endif

            <a href="{{ route('catalogue') }}" class="sidebar-link {{ request()->is('catalogue') ? 'active' : '' }}">
                <i class="fas fa-search"></i> <span>Catalogue</span>
            </a>
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-dashboard" id="navbar">
        <div class="container-fluid h-100">
            <div class="d-flex justify-content-between align-items-center w-100">
                
                <div class="app-brand-title d-none d-md-flex">
                    <i class="fas fa-book-open me-3"></i>
                    <span>Bibliothèque Universitaire</span>
                </div>

                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white d-md-none me-2" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="theme-toggle me-4" style="cursor: pointer;">
                        <button id="theme-btn" class="btn btn-outline-warning btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                            <i id="theme-icon" class="fas fa-moon"></i>
                        </button>
                    </div>

                    <div class="dropdown">
                        <a class="profile-container dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-info">
                                <span class="profile-name">{{ Auth::user()->name }}</span>
                                <span class="profile-role">
                                    @if(Auth::user()->estAdministrateur()) Administrateur
                                    @elseif(Auth::user()->estBibliothecaire()) Bibliothécaire
                                    @else Étudiant
                                    @endif
                                </span>
                            </div>
                            <div class="profile-avatar-wrapper position-relative">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=D4AF37&color=5D4037&bold=true" 
                                     class="profile-avatar-img" alt="Avatar">
                                <div class="online-status" style="position: absolute; bottom: 2px; right: 2px; width: 10px; height: 10px; background-color: #2ecc71; border: 2px solid #3E2723; border-radius: 50%;"></div>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-luxe shadow border-0" aria-labelledby="userDropdown">
                            <div class="dropdown-header-luxe">
                                <p class="mb-0 fw-bold">{{ Auth::user()->name }}</p>
                                <small style="opacity: 0.8; font-size: 0.7rem;">{{ Auth::user()->email }}</small>
                            </div>
                            <li><a class="dropdown-item dropdown-item-luxe" href="{{ route('profile') }}"><i class="fas fa-user-shield me-2"></i>Mon Profil</a></li>
                            <li><hr class="dropdown-divider my-0"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <a class="dropdown-item dropdown-item-luxe text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-power-off me-2"></i>Déconnexion
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content" id="main-content">
        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const navbar = document.getElementById('navbar');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            navbar.classList.toggle('expanded');
            if (window.innerWidth <= 768) { sidebar.classList.toggle('show'); }
        }

        // --- GESTION DU THEME (DARK/LIGHT) ---
        const themeBtn = document.getElementById('theme-btn');
        const themeIcon = document.getElementById('theme-icon');
        const currentTheme = localStorage.getItem('theme') || 'light';

        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            }
        });

        // --- NOTIFICATIONS LUXE (SWEETALERT2) ---
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: "{{ session('success') }}",
                confirmButtonColor: '#D4AF37',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1E1E1E' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#E0D5BA' : '#5D4037'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
                confirmButtonColor: '#5D4037',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1E1E1E' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#E0D5BA' : '#5D4037'
            });
        @endif
    </script>
    @stack('scripts')
</body>
</html>