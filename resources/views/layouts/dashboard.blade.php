<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de Bord - Bibliothèque Universitaire')</title>
    
    {{-- Feuilles de style et scripts empaquetés localement (Bootstrap,
         Font Awesome, Chart.js, SweetAlert2) : aucune dépendance à un CDN. --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        /* =====================================================================
           IDENTITÉ VISUELLE — d'après la maquette BiblioteqUniv
           Bleu marine profond + bleu roi, sur fond clair.
           Les noms de variables historiques sont conservés : toutes les vues
           les utilisent déjà, seules leurs valeurs changent.
           ===================================================================== */
        :root {
            --wood-primary: #123A7A;   /* Bleu marine — sidebar, aplats */
            --accent-gold: #2563EB;    /* Bleu roi — accent principal */
            --accent-leather: #94A3B8; /* Gris ardoise — libellés secondaires */
            --paper-bg: #F4F7FC;       /* Fond de page */
            --text-main: #0F172A;      /* Encre */
            --card-bg: #FFFFFF;
            --navbar-bg: #0F2557;      /* Bleu nuit — barre supérieure */

            --brand-clair: #E8F0FE;    /* Bleu très clair — pastilles d'icônes */
            --bordure: rgba(15, 37, 87, .10);
        }

        /* --- MODE SOMBRE (Bleu nuit) --- */
        [data-theme="dark"] {
            --wood-primary: #14213F;
            --accent-gold: #60A5FA;
            --accent-leather: #64748B;
            --paper-bg: #0B1220;
            --text-main: #E2E8F0;
            --card-bg: #111A2E;
            --navbar-bg: #070E1C;

            --brand-clair: rgba(96, 165, 250, .14);
            --bordure: rgba(226, 232, 240, .12);
        }

        /* --- HABILLAGE BOOTSTRAP AUX COULEURS DE LA MARQUE ---
           Les vues utilisent les classes utilitaires standard ; on redéfinit
           ici leur rendu pour rester fidèle à la maquette. */
        .btn-warning {
            background-color: var(--accent-gold);
            border-color: var(--accent-gold);
            color: #fff;
            font-weight: 500;
        }
        .btn-warning:hover, .btn-warning:focus, .btn-warning:active {
            background-color: #1D4ED8;
            border-color: #1D4ED8;
            color: #fff;
        }
        .btn-outline-warning { color: var(--accent-gold); border-color: var(--accent-gold); }
        .btn-outline-warning:hover { background-color: var(--accent-gold); color: #fff; }
        .badge.bg-warning { background-color: #F59E0B !important; }
        .nav-pills .nav-link.active,
        .list-group-item.active { background-color: var(--accent-gold); border-color: var(--accent-gold); }
        .accordion-button:not(.collapsed) { background-color: var(--brand-clair); color: var(--wood-primary); }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .18);
        }
        .card { border-radius: 14px; }
        .table > :not(caption) > * > * { background-color: transparent; }

        body {
            background-color: var(--paper-bg);
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }

        h1, h2, h3, .navbar-brand, .text-uppercase, .app-brand-title, .sidebar-project-name {
            font-family: 'Inter', system-ui, sans-serif; letter-spacing: -.2px;
        }

        /* --- STYLE SIDEBAR  --- */
        .sidebar-brand-container {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(37, 99, 235, 0.2);
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
            color: #E8F0FE;
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
            color: #0F2557 !important;
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

        /* --- CARTES STATISTIQUES --- */
        .carte-stat { position: relative; border-radius: 14px; transition: transform .2s, box-shadow .2s; }
        .carte-stat:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.2rem rgba(0,0,0,.12) !important; }
        .carte-stat-icone {
            width: 54px; height: 54px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .min-w-0 { min-width: 0; }

        /* --- SOUS-MENU SIDEBAR --- */
        .sidebar-section { margin-top: .75rem; }
        .sidebar.collapsed .sidebar-section-titre { display: none; }
        .sidebar-nav { max-height: calc(100vh - 130px); overflow-y: auto; overflow-x: hidden; padding-bottom: 2rem; }
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(37,99,235,.35); border-radius: 3px; }
        .sidebar-badge { margin-left: auto; font-size: .65rem; }
        .sidebar.collapsed .sidebar-badge { display: none; }

        /* --- RECHERCHE GLOBALE --- */
        .recherche-globale { position: relative; max-width: 360px; width: 100%; }
        .recherche-globale input {
            background: rgba(255,255,255,.12); border: 1px solid rgba(37,99,235,.35);
            color: #fff; border-radius: 30px; padding: .4rem 1rem .4rem 2.3rem;
        }
        .recherche-globale input::placeholder { color: rgba(255,255,255,.55); }
        .recherche-globale input:focus { outline: none; background: rgba(255,255,255,.2); box-shadow: 0 0 0 .2rem rgba(37,99,235,.25); }
        .recherche-globale .fa-magnifying-glass { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: var(--accent-gold); font-size: .85rem; }
        .suggestions-liste {
            position: absolute; top: 110%; left: 0; right: 0; z-index: 1100;
            background: var(--card-bg); color: var(--text-main);
            border-radius: 12px; box-shadow: 0 .6rem 1.5rem rgba(0,0,0,.25); overflow: hidden; display: none;
        }
        .suggestions-liste a { display: block; padding: .6rem .9rem; color: inherit; text-decoration: none; border-bottom: 1px solid rgba(0,0,0,.06); }
        .suggestions-liste a:hover { background: rgba(37,99,235,.15); }

        /* --- CLOCHE DE NOTIFICATIONS --- */
        .cloche-wrapper { position: relative; }
        .cloche-compteur {
            position: absolute; top: -4px; right: -4px; font-size: .6rem;
            min-width: 17px; height: 17px; line-height: 17px; text-align: center;
            border-radius: 9px; background: #c62828; color: #fff; padding: 0 4px;
        }
        .panneau-notifications {
            width: 340px; max-height: 420px; overflow-y: auto; padding: 0;
        }
        .panneau-notifications .item-notification { padding: .7rem .9rem; border-bottom: 1px solid rgba(0,0,0,.06); display: flex; gap: .7rem; }
        .panneau-notifications .item-notification.non-lue { background: rgba(37,99,235,.12); }

        @media (max-width: 768px) {
            .recherche-globale { display: none; }
        }

        /* --- NAVIGATION MOBILE (barre basse, comme sur la maquette) --- */
        .nav-mobile {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 1040;
            background: var(--card-bg);
            border-top: 1px solid var(--bordure);
            box-shadow: 0 -2px 12px rgba(15, 37, 87, .08);
            display: none;
        }
        .nav-mobile a {
            flex: 1; text-align: center; padding: .5rem .25rem .6rem;
            color: var(--accent-leather); text-decoration: none; font-size: .68rem;
        }
        .nav-mobile a i { display: block; font-size: 1.05rem; margin-bottom: .15rem; }
        .nav-mobile a.active { color: var(--accent-gold); font-weight: 600; }

        @media (max-width: 576px) {
            .carte-stat-icone { width: 44px; height: 44px; font-size: 1.05rem; border-radius: 12px; }
            .carte-stat .text-uppercase { letter-spacing: 0 !important; font-size: .7rem; }
            .carte-stat .fs-3 { font-size: 1.5rem !important; }
        }

        @media (max-width: 768px) {
            .nav-mobile { display: flex; }
            .main-content { padding-bottom: 72px; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content, .navbar-dashboard { margin-left: 0; left: 0; }
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
        <div class="mt-2 sidebar-nav">
            @php
                $u = Auth::user();
                $enRetard = $u->estPersonnel() ? \App\Models\Emprunt::enRetard()->count() : 0;
                $demandesRenouvellement = $u->estPersonnel()
                    ? \App\Models\Renouvellement::where('statut', 'en_attente')->count() : 0;
            @endphp

            <h6 class="sidebar-section-titre text-uppercase px-3 mb-2" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Menu principal</h6>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge-high"></i> <span>{{ $u->estEmprunteur() ? 'Mon espace' : 'Tableau de bord' }}</span>
            </a>

            <a href="{{ route('catalogue') }}" class="sidebar-link {{ request()->routeIs('catalogue') ? 'active' : '' }}">
                <i class="fas fa-book-open-reader"></i> <span>Catalogue</span>
            </a>

            <a href="{{ route('recherche') }}" class="sidebar-link {{ request()->routeIs('recherche') ? 'active' : '' }}">
                <i class="fas fa-magnifying-glass"></i> <span>Recherche avancée</span>
            </a>

            @if($u->estEmprunteur())
                <h6 class="sidebar-section-titre text-uppercase px-3 mb-2 sidebar-section" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Mon compte</h6>
                <a href="{{ route('mes-emprunts') }}" class="sidebar-link {{ request()->routeIs('mes-emprunts') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding"></i> <span>Mes emprunts</span>
                </a>
                <a href="{{ route('reservations.index') }}" class="sidebar-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                    <i class="fas fa-bookmark"></i> <span>Mes réservations</span>
                </a>
                <a href="{{ route('penalites.index') }}" class="sidebar-link {{ request()->routeIs('penalites.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> <span>Mes pénalités</span>
                </a>
            @endif

            @can('livres.voir')
                @if($u->estPersonnel())
                    <h6 class="sidebar-section-titre text-uppercase px-3 mb-2 sidebar-section" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Catalogue</h6>
                    <a href="{{ route('livres.index') }}" class="sidebar-link {{ request()->routeIs('livres.*') ? 'active' : '' }}">
                        <i class="fas fa-book"></i> <span>Ouvrages</span>
                    </a>
                    <a href="{{ route('exemplaires.index') }}" class="sidebar-link {{ request()->routeIs('exemplaires.*') ? 'active' : '' }}">
                        <i class="fas fa-barcode"></i> <span>Exemplaires</span>
                    </a>
                    <a href="{{ route('auteurs.index') }}" class="sidebar-link {{ request()->routeIs('auteurs.*') ? 'active' : '' }}">
                        <i class="fas fa-feather-pointed"></i> <span>Auteurs</span>
                    </a>
                    <a href="{{ route('editeurs.index') }}" class="sidebar-link {{ request()->routeIs('editeurs.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i> <span>Éditeurs</span>
                    </a>
                    <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> <span>Catégories</span>
                    </a>
                    <a href="{{ route('localisations.index') }}" class="sidebar-link {{ request()->routeIs('localisations.*') ? 'active' : '' }}">
                        <i class="fas fa-map-location-dot"></i> <span>Rayons</span>
                    </a>
                @endif
            @endcan

            @if($u->estPersonnel())
                <h6 class="sidebar-section-titre text-uppercase px-3 mb-2 sidebar-section" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Circulation</h6>
                <a href="{{ route('emprunts.index') }}" class="sidebar-link {{ request()->routeIs('emprunts.index') || request()->routeIs('emprunts.show') || request()->routeIs('emprunts.create') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding"></i> <span>Emprunts</span>
                    @if($enRetard > 0)<span class="badge bg-danger sidebar-badge">{{ $enRetard }}</span>@endif
                </a>
                <a href="{{ route('emprunts.guichet') }}" class="sidebar-link {{ request()->routeIs('emprunts.guichet') ? 'active' : '' }}">
                    <i class="fas fa-barcode"></i> <span>Guichet de retour</span>
                </a>
                <a href="{{ route('reservations.index') }}" class="sidebar-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                    <i class="fas fa-bookmark"></i> <span>Réservations</span>
                </a>
                <a href="{{ route('renouvellements.index') }}" class="sidebar-link {{ request()->routeIs('renouvellements.*') ? 'active' : '' }}">
                    <i class="fas fa-arrows-rotate"></i> <span>Renouvellements</span>
                    @if($demandesRenouvellement > 0)<span class="badge bg-warning text-dark sidebar-badge">{{ $demandesRenouvellement }}</span>@endif
                </a>
                <a href="{{ route('penalites.index') }}" class="sidebar-link {{ request()->routeIs('penalites.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> <span>Pénalités</span>
                </a>

                <h6 class="sidebar-section-titre text-uppercase px-3 mb-2 sidebar-section" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Usagers</h6>
                <a href="{{ route('etudiants.index') }}" class="sidebar-link {{ request()->routeIs('etudiants.*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i> <span>Étudiants</span>
                </a>
                @can('usagers.voir')
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-gear"></i> <span>Tous les comptes</span>
                    </a>
                @endcan

                <h6 class="sidebar-section-titre text-uppercase px-3 mb-2 sidebar-section" style="color: var(--accent-leather); font-size: 0.7rem; letter-spacing: 1px;">Pilotage</h6>
                @can('statistiques.voir')
                    <a href="{{ route('statistiques') }}" class="sidebar-link {{ request()->routeIs('statistiques') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> <span>Statistiques</span>
                    </a>
                @endcan
                @can('rapports.generer')
                    <a href="{{ route('rapports.index') }}" class="sidebar-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
                        <i class="fas fa-file-lines"></i> <span>Rapports</span>
                    </a>
                @endcan
                @can('audit.voir')
                    <a href="{{ route('audit.index') }}" class="sidebar-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i> <span>Journal d'activité</span>
                    </a>
                @endcan
                @can('parametres.gerer')
                    <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fas fa-sliders"></i> <span>Paramètres</span>
                    </a>
                @endcan
            @endif
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-dashboard" id="navbar">
        <div class="container-fluid h-100">
            <div class="d-flex justify-content-between align-items-center w-100">
                
                <div class="d-flex align-items-center gap-4 flex-grow-1">
                    <div class="app-brand-title d-none d-lg-flex">
                        <i class="fas fa-book-open me-3"></i>
                        <span>Bibliothèque Universitaire</span>
                    </div>

                    {{-- Recherche unique : titre, auteur, ISBN, code-barres, mot-clé --}}
                    <form class="recherche-globale" action="{{ route('recherche') }}" method="GET" autocomplete="off">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" name="q" id="recherche-globale-champ"
                               value="{{ request('q') }}"
                               placeholder="Rechercher un titre, un auteur, un ISBN, un code-barres...">
                        <div class="suggestions-liste" id="suggestions-liste"></div>
                    </form>
                </div>

                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white d-md-none me-2" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>

                    {{-- Centre de notifications --}}
                    <div class="dropdown cloche-wrapper me-3">
                        <button class="btn btn-link text-white position-relative p-1" id="cloche-notifications"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="fas fa-bell fs-5"></i>
                            <span class="cloche-compteur d-none" id="cloche-compteur">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 panneau-notifications"
                             aria-labelledby="cloche-notifications">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2"
                                 style="background: var(--wood-primary); color: #fff;">
                                <strong class="small">Notifications</strong>
                                <a href="{{ route('notifications.index') }}" class="small text-warning text-decoration-none">Tout voir</a>
                            </div>
                            <div id="liste-notifications">
                                <div class="text-center py-4 small" style="opacity:.6;">Chargement...</div>
                            </div>
                        </div>
                    </div>

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
                                <img src="{{ Auth::user()->url_photo }}" 
                                     class="profile-avatar-img" alt="Avatar">
                                <div class="online-status" style="position: absolute; bottom: 2px; right: 2px; width: 10px; height: 10px; background-color: #2ecc71; border: 2px solid var(--navbar-bg); border-radius: 50%;"></div>
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

    {{-- Navigation principale sur mobile --}}
    <nav class="nav-mobile" aria-label="Navigation principale mobile">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-house"></i> Accueil
        </a>
        <a href="{{ route('catalogue') }}" class="{{ request()->routeIs('catalogue') || request()->routeIs('livres.*') ? 'active' : '' }}">
            <i class="fas fa-book-open-reader"></i> Catalogue
        </a>
        @if(Auth::user()->estEmprunteur())
            <a href="{{ route('mes-emprunts') }}" class="{{ request()->routeIs('mes-emprunts') ? 'active' : '' }}">
                <i class="fas fa-hand-holding"></i> Emprunts
            </a>
            <a href="{{ route('reservations.index') }}" class="{{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                <i class="fas fa-bookmark"></i> Réservations
            </a>
        @else
            <a href="{{ route('emprunts.index') }}" class="{{ request()->routeIs('emprunts.index') ? 'active' : '' }}">
                <i class="fas fa-hand-holding"></i> Emprunts
            </a>
            <a href="{{ route('emprunts.guichet') }}" class="{{ request()->routeIs('emprunts.guichet') ? 'active' : '' }}">
                <i class="fas fa-barcode"></i> Scanner
            </a>
        @endif
        <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile*') ? 'active' : '' }}">
            <i class="fas fa-user"></i> Profil
        </a>
    </nav>

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

        // --- NOTIFICATIONS (SWEETALERT2) ---
        // Le bundle Vite est un module : ses globales ne sont disponibles
        // qu'une fois la page complètement chargée.
        window.addEventListener('load', function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: "{{ session('success') }}",
                confirmButtonColor: '#2563EB',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#111A2E' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#E2E8F0' : '#0F172A'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
                confirmButtonColor: '#123A7A',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#111A2E' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#E2E8F0' : '#0F172A'
            });
        @endif
        });
    </script>
    <script>
        // --- SUGGESTIONS DE RECHERCHE ---
        (function () {
            const champ = document.getElementById('recherche-globale-champ');
            const liste = document.getElementById('suggestions-liste');
            if (!champ || !liste) return;

            let minuteur = null;

            champ.addEventListener('input', function () {
                clearTimeout(minuteur);
                const terme = this.value.trim();

                if (terme.length < 2) {
                    liste.style.display = 'none';
                    return;
                }

                // Anti-rebond : on n'interroge le serveur qu'après une pause de saisie.
                minuteur = setTimeout(() => {
                    fetch("{{ route('recherche.suggestions') }}?q=" + encodeURIComponent(terme), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(reponse => reponse.ok ? reponse.json() : { resultats: [] })
                        .then(donnees => {
                            if (!donnees.resultats.length) {
                                liste.innerHTML = '<div class="px-3 py-2 small text-muted">Aucun résultat.</div>';
                            } else {
                                liste.innerHTML = donnees.resultats.map(r => `
                                    <a href="${r.url}">
                                        <div class="fw-semibold small">${r.titre}</div>
                                        <div class="small" style="opacity:.7;">${r.auteur ?? ''} · ${r.categorie ?? ''}
                                            ${r.disponible ? '<span class="text-success">• disponible</span>' : '<span class="text-danger">• indisponible</span>'}
                                        </div>
                                    </a>`).join('');
                            }
                            liste.style.display = 'block';
                        })
                        .catch(() => { liste.style.display = 'none'; });
                }, 250);
            });

            document.addEventListener('click', e => {
                if (!champ.contains(e.target) && !liste.contains(e.target)) {
                    liste.style.display = 'none';
                }
            });
        })();

        // --- CENTRE DE NOTIFICATIONS ---
        (function () {
            const compteur = document.getElementById('cloche-compteur');
            const conteneur = document.getElementById('liste-notifications');
            if (!compteur || !conteneur) return;

            function charger() {
                fetch("{{ route('notifications.recentes') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.ok ? r.json() : null)
                    .then(donnees => {
                        if (!donnees) return;

                        if (donnees.non_lues > 0) {
                            compteur.textContent = donnees.non_lues > 99 ? '99+' : donnees.non_lues;
                            compteur.classList.remove('d-none');
                        } else {
                            compteur.classList.add('d-none');
                        }

                        conteneur.innerHTML = donnees.notifications.length
                            ? donnees.notifications.map(n => `
                                <a href="${n.url}" class="item-notification text-decoration-none ${n.lue ? '' : 'non-lue'}"
                                   style="color: var(--text-main);">
                                    <i class="fas ${n.icone} text-${n.couleur} mt-1"></i>
                                    <span class="flex-grow-1">
                                        <span class="d-block fw-semibold small">${n.titre}</span>
                                        <span class="d-block small" style="opacity:.75;">${n.message}</span>
                                        <span class="d-block small" style="opacity:.5;">${n.date}</span>
                                    </span>
                                </a>`).join('')
                            : '<div class="text-center py-4 small" style="opacity:.6;">Aucune notification.</div>';
                    })
                    .catch(() => {});
            }

            charger();
            setInterval(charger, 60000);
        })();
    </script>

    @stack('scripts')
</body>
</html>