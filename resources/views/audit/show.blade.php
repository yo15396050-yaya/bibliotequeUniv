@extends('layouts.dashboard')
@section('title', 'Entrée du journal #' . $journal->id)

@section('content')
<x-entete-page :titre="'Entrée #' . $journal->id" icone="fa-clipboard-list" :sous-titre="$journal->description">
    <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <dl class="row small mb-0">
                <dt class="col-5">Date</dt><dd class="col-7">{{ $journal->created_at->format('d/m/Y à H:i:s') }}</dd>
                <dt class="col-5">Utilisateur</dt><dd class="col-7">{{ $journal->user?->name ?? 'Système' }}</dd>
                <dt class="col-5">Action</dt><dd class="col-7">{{ $journal->action }}</dd>
                <dt class="col-5">Module</dt><dd class="col-7">{{ $journal->module }}</dd>
                <dt class="col-5">Objet</dt><dd class="col-7">{{ class_basename($journal->sujet_type ?? '—') }} #{{ $journal->sujet_id ?? '—' }}</dd>
                <dt class="col-5">Adresse IP</dt><dd class="col-7">{{ $journal->adresse_ip ?? '—' }}</dd>
                <dt class="col-5">Navigateur</dt><dd class="col-7 text-break small">{{ $journal->user_agent ?? '—' }}</dd>
            </dl>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0">Données de l'opération</h6></div>
            <div class="card-body">
                @if($journal->donnees)
                    <pre class="small mb-0" style="white-space:pre-wrap; word-break:break-word;">{{ json_encode($journal->donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                @else
                    <x-vide message="Aucune donnée complémentaire." icone="fa-database" />
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
