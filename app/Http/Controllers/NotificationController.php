<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()
            ->when($request->input('filtre') === 'non_lues', fn ($q) => $q->whereNull('read_at'))
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    /** Alimente la cloche de la barre de navigation. */
    public function recentes()
    {
        $user = Auth::user();

        return response()->json([
            'non_lues' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->take(8)->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'titre' => $notification->data['titre'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? route('notifications.index'),
                    'icone' => $notification->data['icone'] ?? 'fa-bell',
                    'couleur' => $notification->data['couleur'] ?? 'primary',
                    'lue' => $notification->read_at !== null,
                    'date' => $notification->created_at->diffForHumans(),
                ]),
        ]);
    }

    public function marquerLue(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'url' => $url]);
        }

        return $url ? redirect($url) : back();
    }

    public function toutMarquerLues()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notification supprimée.');
    }
}
