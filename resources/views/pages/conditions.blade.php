@extends('layouts.public')
@section('title', "Conditions d'utilisation — BiblioteqUniv")

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-4">Conditions d'utilisation</h1>

            <div class="carte p-4 p-md-5">
                @php $devise = \App\Support\Parametres::devise(); @endphp

                <h5 class="fw-semibold">1. Objet</h5>
                <p class="text-muted small">
                    Les présentes conditions régissent l'accès et l'utilisation du service BiblioteqUniv,
                    application de gestion de la {{ $nomBibliotheque }}. Toute connexion au service vaut
                    acceptation de ces conditions.
                </p>

                <h5 class="fw-semibold mt-4">2. Accès au service</h5>
                <p class="text-muted small">
                    L'accès est réservé aux étudiants, enseignants et personnels régulièrement inscrits.
                    Les comptes sont créés par la bibliothèque ; l'inscription publique n'est pas ouverte.
                    Chaque usager est responsable de la confidentialité de ses identifiants et des
                    opérations effectuées depuis son compte.
                </p>

                <h5 class="fw-semibold mt-4">3. Emprunts et restitutions</h5>
                <p class="text-muted small">
                    Les quotas et durées d'emprunt sont fixés par la bibliothèque et peuvent être modifiés.
                    L'usager s'engage à restituer les ouvrages avant l'échéance indiquée et à les rendre
                    dans l'état où ils lui ont été confiés.
                </p>

                <h5 class="fw-semibold mt-4">4. Retards, pertes et dégradations</h5>
                <p class="text-muted small">
                    Tout retard entraîne une pénalité calculée par jour de retard. La perte ou la dégradation
                    d'un exemplaire donne lieu à une pénalité forfaitaire. Au-delà du seuil de dette fixé par
                    la bibliothèque ({{ \App\Support\Parametres::formaterMontant(\App\Support\Parametres::decimal('penalite.seuil_blocage', 1000)) }}),
                    les nouveaux emprunts sont suspendus jusqu'à régularisation.
                </p>

                <h5 class="fw-semibold mt-4">5. Documents numériques</h5>
                <p class="text-muted small">
                    Les documents numériques sont mis à disposition à des fins strictement pédagogiques et
                    personnelles. Leur rediffusion, leur mise en ligne ou leur exploitation commerciale sont
                    interdites. Les accès et téléchargements sont journalisés.
                </p>

                <h5 class="fw-semibold mt-4">6. Données personnelles</h5>
                <p class="text-muted small">
                    Les données collectées (identité, coordonnées, rattachement académique, historique de
                    circulation) servent exclusivement à la gestion de la bibliothèque. Les opérations
                    sensibles sont tracées dans un journal d'activité consultable par l'administration.
                    Vous pouvez demander la rectification de vos données auprès du service.
                </p>

                <h5 class="fw-semibold mt-4">7. Suspension du compte</h5>
                <p class="text-muted small">
                    La bibliothèque peut suspendre un compte en cas de non-respect des présentes conditions,
                    de dette persistante ou d'usage frauduleux du service. La suspension prend effet
                    immédiatement et déconnecte les sessions en cours.
                </p>

                <p class="text-muted small mb-0 mt-4">
                    Dernière mise à jour : {{ now()->translatedFormat('d F Y') }}.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
