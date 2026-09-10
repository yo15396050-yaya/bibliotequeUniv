@extends('layouts.dashboard')

@section('title', 'Lecture — ' . $livre->titre)

@section('content')
@php
    $document = $document ?? $livre->documents()->first();
    $telechargeable = $document
        ? ($document->autoriser_telechargement && Auth::user()->can('download', $document))
        : ($livre->autoriser_telechargement && $livre->estDisponibleNumerique());
@endphp

<x-entete-page :titre="$livre->titre" icone="fa-book-open"
    :sous-titre="$livre->auteur . ' · ' . $livre->libelle_type">
    @if($telechargeable)
        <a href="{{ route('livres.download', $livre) }}" class="btn btn-warning">
            <i class="fas fa-download me-1"></i> Télécharger
        </a>
    @endif
    <a href="{{ route('livres.show', $livre) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour à la fiche
    </a>
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        {{-- La liseuse pointe vers la route protégée : le fichier reste sur le
             disque privé et l'accès est vérifié par la policy à chaque requête. --}}
        <iframe src="{{ route('livres.stream', $livre) }}"
                title="Lecture de « {{ $livre->titre }} »"
                class="w-100 border-0 rounded"
                style="height: 78vh; min-height: 460px; background: #525659;"></iframe>
    </div>
    <div class="card-footer bg-transparent small d-flex flex-wrap justify-content-between gap-2">
        <span style="opacity:.7;">
            <i class="fas fa-lock me-1"></i>
            Document réservé à un usage pédagogique et personnel.
        </span>
        @if($document)
            <span style="opacity:.7;">
                {{ strtoupper($document->format) }} · {{ $document->taille_lisible }}
            </span>
        @endif
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm mt-3 d-flex align-items-center gap-3">
    <i class="fas fa-circle-info fa-lg"></i>
    <div class="small">
        Si le document ne s'affiche pas, votre navigateur ne dispose peut-être pas de lecteur intégré :
        @if($telechargeable)
            <a href="{{ route('livres.download', $livre) }}">téléchargez le fichier</a> pour l'ouvrir avec votre lecteur habituel.
        @else
            rapprochez-vous de la bibliothèque, le téléchargement n'est pas autorisé pour ce document.
        @endif
    </div>
</div>
@endsection
