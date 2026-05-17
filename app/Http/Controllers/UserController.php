<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ... autres méthodes existantes (index, create, store, show, edit, update, destroy)
    
    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function profile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
        ]);
        
        // Mise à jour des informations de base
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? $user->telephone,
            'adresse' => $validated['adresse'] ?? $user->adresse,
        ]);
        
        // Mise à jour du mot de passe si fourni
        if (!empty($validated['current_password']) && !empty($validated['new_password'])) {
            if (Hash::check($validated['current_password'], $user->password)) {
                $user->update([
                    'password' => Hash::make($validated['new_password'])
                ]);
            } else {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect']);
            }
        }
        
        return redirect()->route('profile')
            ->with('success', 'Profil mis à jour avec succès!');
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_active' => !$user->is_active
        ]);
        
        $status = $user->is_active ? 'activé' : 'désactivé';
        return redirect()->back()
            ->with('success', "Utilisateur {$status} avec succès!");
    }
}