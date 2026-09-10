@extends('layouts.public')
@section('title', "Guide d'utilisation — BiblioteqUniv")

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <div class="pastille mx-auto mb-3"><i class="fas fa-book"></i></div>
                <h1 class="fw-bold">Guide d'utilisation</h1>
                <p class="text-muted mb-0">Découvrez pas à pas comment profiter de BiblioteqUniv.</p>
            </div>

            @php
                $etapes = [
                    ['fa-arrow-right-to-bracket', 'Se connecter',
                     "Connectez-vous avec votre adresse universitaire ou votre matricule. Le mot de passe provisoire remis par la bibliothèque est à changer depuis « Mon profil »."],
                    ['fa-magnifying-glass', 'Chercher un ouvrage',
                     "Une seule barre de recherche interroge les titres, sous-titres, auteurs, ISBN, éditeurs, mots-clés et codes-barres. Affinez ensuite par catégorie, langue, année, type de document ou disponibilité."],
                    ['fa-circle-info', 'Consulter une fiche',
                     "La fiche affiche le résumé, les auteurs, l'éditeur, l'emplacement dans les rayons, le nombre d'exemplaires disponibles et, le cas échéant, la version numérique."],
                    ['fa-hand-holding', 'Emprunter',
                     "Présentez-vous au guichet : le bibliothécaire scanne le code-barres de l'exemplaire. Le système vérifie automatiquement votre quota, vos retards et vos pénalités avant de valider."],
                    ['fa-bookmark', 'Réserver',
                     "Si tous les exemplaires sont sortis, réservez l'ouvrage : vous entrez dans une file d'attente et êtes prévenu dès qu'un exemplaire est mis de côté pour vous."],
                    ['fa-arrows-rotate', 'Renouveler',
                     "Depuis « Mes emprunts », demandez un renouvellement tant que l'ouvrage n'est ni en retard ni réservé par un autre usager."],
                    ['fa-rotate-left', 'Rendre un ouvrage',
                     "Au retour, le scan du code-barres retrouve l'emprunt, enregistre la date, calcule un éventuel retard et remet l'exemplaire en circulation."],
                    ['fa-money-bill-wave', 'Régler une pénalité',
                     "Les pénalités apparaissent dans « Mes pénalités ». Le règlement s'effectue au guichet : un reçu PDF vous est remis, et les paiements partiels sont acceptés."],
                    ['fa-file-pdf', 'Lire un document numérique',
                     "Les ouvrages numérisés s'ouvrent dans la liseuse intégrée. Les fichiers sont stockés dans un espace privé : seuls les usagers autorisés peuvent les consulter ou les télécharger."],
                ];
            @endphp

            @foreach($etapes as $index => [$icone, $titre, $texte])
                <div class="carte p-4 mb-3 d-flex gap-3">
                    <div class="pastille flex-shrink-0"><i class="fas {{ $icone }}"></i></div>
                    <div>
                        <div class="small text-muted">Étape {{ $index + 1 }}</div>
                        <h5 class="fw-semibold mb-1">{{ $titre }}</h5>
                        <p class="text-muted small mb-0">{{ $texte }}</p>
                    </div>
                </div>
            @endforeach

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="btn btn-marque px-5">Commencer</a>
            </div>
        </div>
    </div>
</div>
@endsection
