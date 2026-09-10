@extends('layouts.dashboard')
@section('title', 'Notifications')

@section('content')
<x-entete-page titre="Notifications" icone="fa-bell"
    :sous-titre="Auth::user()->unreadNotifications()->count() . ' non lue(s)'">
    <form action="{{ route('notifications.toutes-lues') }}" method="POST">
        @csrf
        <button class="btn btn-outline-secondary"><i class="fas fa-check-double me-1"></i> Tout marquer comme lu</button>
    </form>
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ request('filtre') !== 'non_lues' ? 'active' : '' }}" href="{{ route('notifications.index') }}">Toutes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('filtre') === 'non_lues' ? 'active' : '' }}"
                   href="{{ route('notifications.index', ['filtre' => 'non_lues']) }}">Non lues</a>
            </li>
        </ul>

        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php $donnees = $notification->data; @endphp
                <div class="list-group-item d-flex gap-3 align-items-start {{ $notification->read_at ? '' : 'bg-warning-subtle' }}">
                    <i class="fas {{ $donnees['icone'] ?? 'fa-bell' }} fa-lg text-{{ $donnees['couleur'] ?? 'secondary' }} mt-1"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ $donnees['titre'] ?? 'Notification' }}</div>
                        <div class="small" style="opacity:.8;">{{ $donnees['message'] ?? '' }}</div>
                        <div class="small" style="opacity:.55;">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex flex-column gap-1 flex-shrink-0">
                        @if(!empty($donnees['url']))
                            <form action="{{ route('notifications.lue', $notification->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <x-vide message="Aucune notification." icone="fa-bell-slash" />
            @endforelse
        </div>

        <div class="mt-3">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
