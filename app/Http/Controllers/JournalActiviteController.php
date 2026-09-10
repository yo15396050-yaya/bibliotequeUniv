<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\User;
use Illuminate\Http\Request;

class JournalActiviteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:audit.voir');
    }

    public function index(Request $request)
    {
        $journaux = JournalActivite::with('user:id,name,prenom,role')
            ->module($request->input('module'))
            ->action($request->input('action'))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->when($request->filled('search'), fn ($q) => $q->where('description', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('audit.index', [
            'journaux' => $journaux,
            'modules' => JournalActivite::select('module')->distinct()->pluck('module')->sort()->values(),
            'actions' => JournalActivite::select('action')->distinct()->pluck('action')->sort()->values(),
            'utilisateurs' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_BIBLIOTHECAIRE])
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(JournalActivite $journal)
    {
        $journal->load('user');

        return view('audit.show', compact('journal'));
    }
}
