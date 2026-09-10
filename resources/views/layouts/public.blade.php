<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BiblioteqUniv')</title>


    {{-- Feuilles de style et scripts empaquetés localement (Bootstrap,
         Font Awesome, Chart.js, SweetAlert2) : aucune dépendance à un CDN. --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --bleu-nuit: #0F2557;
            --bleu-marine: #123A7A;
            --bleu-roi: #2563EB;
            --bleu-clair: #E8F0FE;
            --fond: #F4F7FC;
            --encre: #0F172A;
            --ardoise: #64748B;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--fond);
            color: var(--encre);
        }

        .navbar-public {
            background: var(--bleu-nuit);
            padding: .85rem 0;
        }
        .navbar-public .navbar-brand { color: #fff; font-weight: 700; letter-spacing: .3px; }
        .navbar-public .nav-link { color: rgba(255,255,255,.82); font-size: .93rem; }
        .navbar-public .nav-link:hover, .navbar-public .nav-link.active { color: #fff; }

        .btn-marque {
            background: var(--bleu-roi); border: none; color: #fff; font-weight: 500;
            border-radius: 10px; padding: .6rem 1.4rem;
        }
        .btn-marque:hover { background: #1D4ED8; color: #fff; }
        .btn-marque-clair {
            background: #fff; color: var(--bleu-marine); border: 1px solid rgba(255,255,255,.5);
            border-radius: 10px; padding: .6rem 1.4rem; font-weight: 500;
        }
        .btn-marque-clair:hover { background: var(--bleu-clair); color: var(--bleu-nuit); }

        .carte { background: #fff; border: 1px solid rgba(15,37,87,.08); border-radius: 16px; }
        .pastille {
            width: 54px; height: 54px; border-radius: 16px; background: var(--bleu-clair);
            color: var(--bleu-roi); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }

        .pied { background: var(--bleu-nuit); color: rgba(255,255,255,.75); padding: 2.5rem 0 1.5rem; }
        .pied a { color: rgba(255,255,255,.75); text-decoration: none; }
        .pied a:hover { color: #fff; }

        .accordion-button:not(.collapsed) { background: var(--bleu-clair); color: var(--bleu-marine); }
        .accordion-button:focus { box-shadow: 0 0 0 .2rem rgba(37,99,235,.18); }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-public sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('accueil') }}">
            <i class="fas fa-book-open"></i> BiblioteqUniv
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#menu-public">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="menu-public">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}" href="{{ route('guide') }}">Guide</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">FAQ</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('aide') ? 'active' : '' }}" href="{{ route('aide') }}">Aide &amp; support</a></li>
                <li class="nav-item ms-lg-2">
                    @auth
                        <a class="btn btn-marque-clair btn-sm" href="{{ route('dashboard') }}">Mon espace</a>
                    @else
                        <a class="btn btn-marque-clair btn-sm" href="{{ route('login') }}">Se connecter</a>
                    @endauth
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1">
    @yield('content')
</main>

<footer class="pied mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h5 class="text-white d-flex align-items-center gap-2"><i class="fas fa-book-open"></i> BiblioteqUniv</h5>
                <p class="small mb-0">
                    La bibliothèque universitaire au bout des doigts : catalogue, emprunts,
                    réservations et documents numériques réunis dans une seule application.
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Ressources</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('guide') }}">Guide d'utilisation</a></li>
                    <li><a href="{{ route('faq') }}">Questions fréquentes</a></li>
                    <li><a href="{{ route('conditions') }}">Conditions d'utilisation</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white">Contact</h6>
                <ul class="list-unstyled small mb-0">
                    <li><i class="fas fa-envelope me-2"></i>{{ \App\Support\Parametres::chaine('general.email_contact', '') }}</li>
                    <li><i class="fas fa-phone me-2"></i>{{ \App\Support\Parametres::chaine('general.telephone', '') }}</li>
                </ul>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,.15);">
        <div class="small text-center">© {{ date('Y') }} {{ \App\Support\Parametres::chaine('general.universite', 'Université') }} — Tous droits réservés.</div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
