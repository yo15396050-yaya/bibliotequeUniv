@extends('layouts.dashboard')
@section('title', 'Nouveau compte')
@section('content')
<x-entete-page titre="Nouveau compte" icone="fa-user-plus">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>
<x-erreurs />
<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('users._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Créer le compte</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
