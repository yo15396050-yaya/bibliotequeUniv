@extends('layouts.public')
@section('title', 'Aide & support — BiblioteqUniv')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <div class="pastille mx-auto mb-3"><i class="fas fa-headset"></i></div>
                <h1 class="fw-bold">Aide &amp; support</h1>
                <p class="text-muted mb-0">Notre équipe est disponible pour vous accompagner.</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <a href="{{ route('faq') }}" class="carte p-4 d-flex align-items-center gap-3 text-decoration-none text-dark h-100">
                        <div class="pastille flex-shrink-0"><i class="fas fa-circle-question"></i></div>
                        <div>
                            <div class="fw-semibold">Questions fréquentes</div>
                            <div class="small text-muted">Emprunts, retards, réservations, mot de passe…</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('guide') }}" class="carte p-4 d-flex align-items-center gap-3 text-decoration-none text-dark h-100">
                        <div class="pastille flex-shrink-0"><i class="fas fa-book"></i></div>
                        <div>
                            <div class="fw-semibold">Guide d'utilisation</div>
                            <div class="small text-muted">Toutes les fonctionnalités, pas à pas.</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="carte p-4 d-flex align-items-center gap-3 h-100">
                        <div class="pastille flex-shrink-0"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="fw-semibold">Nous écrire</div>
                            <div class="small text-muted">{{ $email ?: 'Adresse non renseignée' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="carte p-4 d-flex align-items-center gap-3 h-100">
                        <div class="pastille flex-shrink-0"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="fw-semibold">Nous appeler</div>
                            <div class="small text-muted">{{ $telephone ?: 'Numéro non renseigné' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carte p-4">
                <h5 class="fw-semibold mb-3"><i class="fas fa-triangle-exclamation me-2" style="color: var(--bleu-roi);"></i>Signaler un problème</h5>
                <p class="text-muted small">
                    Ouvrage introuvable, exemplaire endommagé, erreur sur votre compte ou sur une pénalité :
                    présentez-vous au guichet de la bibliothèque muni de votre matricule, ou écrivez-nous
                    en précisant votre nom, votre matricule et la nature du problème. Chaque signalement est
                    tracé dans le journal d'activité et traité par un bibliothécaire.
                </p>
                <p class="text-muted small mb-0">
                    <strong>Horaires du guichet :</strong> du lundi au vendredi, de 08h00 à 18h00.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
