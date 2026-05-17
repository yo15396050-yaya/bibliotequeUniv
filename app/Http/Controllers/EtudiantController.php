<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EtudiantController extends Controller
{
    /**
     * Affiche la liste des étudiants
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'etudiant');
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('matricule', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('filiere', 'like', "%{$request->search}%");
            });
        }
        
        $etudiants = $query->orderBy('name')->paginate(15);
        
        if ($request->ajax()) {
            return view('etudiants.partials._table', compact('etudiants'))->render();
        }
        
        return view('etudiants.index', compact('etudiants'));
    }

    /**
     * Affiche le formulaire de création d'un étudiant
     */
    public function create()
    {
        return view('etudiants.create');
    }

    /**
     * Stocke un nouvel étudiant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'matricule' => 'required|string|unique:users,matricule',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'filiere' => 'nullable|string|max:100',
            'niveau' => 'nullable|string|max:50',
        ]);

        // Créez l'utilisateur avec le rôle étudiant
        $etudiant = User::create(array_merge($validated, [
            'role' => 'etudiant',
            'password' => Hash::make($validated['matricule']), // Mot de passe par défaut = matricule
            'name' => $validated['prenom'] . ' ' . $validated['nom']
        ]));

        return redirect()->route('etudiants.show', $etudiant)
            ->with('success', 'Étudiant créé avec succès! Le mot de passe par défaut est le matricule: ' . $validated['matricule']);
    }

    /**
     * Affiche les détails d'un étudiant
     */
    public function show($id)
    {
        $etudiant = User::with(['emprunts.livre' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        return view('etudiants.show', compact('etudiant'));
    }

    /**
     * Affiche le formulaire d'édition d'un étudiant
     */
    public function edit($id)
    {
        $etudiant = User::findOrFail($id);
        return view('etudiants.edit', compact('etudiant'));
    }

    /**
     * Met à jour un étudiant
     */
    public function update(Request $request, $id)
    {
        $etudiant = User::findOrFail($id);
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $etudiant->id,
            'matricule' => 'required|string|unique:users,matricule,' . $etudiant->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'filiere' => 'nullable|string|max:100',
            'niveau' => 'nullable|string|max:50',
        ]);

        $etudiant->update(array_merge($validated, [
            'name' => $validated['prenom'] . ' ' . $validated['nom']
        ]));

        return redirect()->route('etudiants.show', $etudiant)
            ->with('success', 'Étudiant mis à jour avec succès!');
    }

    /**
     * Supprime un étudiant
     */
    public function destroy($id)
    {
        $etudiant = User::findOrFail($id);
        $etudiant->delete();

        return redirect()->route('etudiants.index')
            ->with('success', 'Étudiant supprimé avec succès!');
    }
}