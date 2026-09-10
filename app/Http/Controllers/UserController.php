<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AnneeAcademique;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::withCount('emprunts')
            ->recherche($request->input('search'))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => User::ROLES,
            'statuts' => User::STATUTS,
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->donneesFormulaire());
    }

    public function store(StoreUserRequest $request)
    {
        $donnees = $request->validated();

        if ($request->hasFile('photo')) {
            $donnees['photo'] = $request->file('photo')->store('utilisateurs', 'public');
        }

        // Mot de passe par défaut = matricule (à changer à la première connexion).
        $motDePasseGenere = empty($donnees['password']);
        $donnees['password'] = $donnees['password'] ?: $donnees['matricule'];
        $donnees['actif'] = true;
        $donnees['email_verified_at'] = now();

        $user = User::create($donnees);
        $this->audit->creation($user, "Compte « {$user->name} » ({$user->libelle_role}) créé", 'usagers');

        return redirect()->route('users.show', $user)->with('success', $motDePasseGenere
            ? "Compte créé. Mot de passe provisoire : {$user->matricule}"
            : 'Compte créé.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load([
            'roles',
            'emprunts' => fn ($q) => $q->with('livre:id,titre,auteur')->latest('date_emprunt')->take(10),
            'reservations' => fn ($q) => $q->with('livre:id,titre')->actives(),
            'penalites' => fn ($q) => $q->latest()->take(10),
            'anneeAcademique',
        ]);

        return view('users.show', [
            'user' => $user,
            'statistiques' => [
                'emprunts_total' => $user->emprunts()->count(),
                'emprunts_en_cours' => $user->emprunts()->enCours()->count(),
                'emprunts_en_retard' => $user->emprunts()->enRetard()->count(),
                'dette' => (float) $user->penalitesBloquantes()->sum('montant')
                    - (float) $user->penalitesBloquantes()->sum('montant_paye'),
            ],
            'motifsBlocage' => $user->estEmprunteur() ? $user->motifsBlocageEmprunt() : [],
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', array_merge($this->donneesFormulaire(), [
            'user' => $user->load('roles'),
            'permissions' => Permissions::CATALOGUE,
        ]));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $donnees = $request->validated();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $donnees['photo'] = $request->file('photo')->store('utilisateurs', 'public');
        }

        // Le mot de passe n'est remplacé que s'il est explicitement fourni.
        if (empty($donnees['password'])) {
            unset($donnees['password']);
        }

        $user->update($donnees);
        $this->audit->modification($user, "Compte « {$user->name} » modifié", 'usagers');

        return redirect()->route('users.show', $user)->with('success', 'Compte mis à jour.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->emprunts()->enCours()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce compte : des emprunts sont en cours.');
        }

        $this->audit->suppression($user, "Compte « {$user->name} » supprimé", 'usagers');
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Compte supprimé.');
    }

    /** Active / désactive un compte. */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update([
            'actif' => ! $user->actif,
            'statut' => $user->actif ? 'suspendu' : 'actif',
        ]);

        $this->audit->enregistrer(
            'modification',
            "Compte « {$user->name} » ".($user->actif ? 'activé' : 'désactivé'),
            $user,
            ['actif' => $user->actif],
            'usagers'
        );

        return back()->with('success', 'Compte '.($user->actif ? 'activé' : 'désactivé').'.');
    }

    /** Attribution des rôles additionnels (permissions fines). */
    public function updateRoles(Request $request, User $user)
    {
        $this->authorize('gererRoles', $user);

        $donnees = $request->validate([
            'role' => ['required', 'in:'.implode(',', array_keys(User::ROLES))],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->update(['role' => $donnees['role']]);
        $user->roles()->sync($donnees['roles'] ?? []);

        $this->audit->enregistrer(
            'permission',
            "Rôles de « {$user->name} » modifiés (principal : {$user->libelle_role})",
            $user,
            ['role' => $donnees['role'], 'roles' => $donnees['roles'] ?? []],
            'usagers'
        );

        return back()->with('success', 'Rôles mis à jour.');
    }

    /** Réinitialise le mot de passe et renvoie une valeur provisoire. */
    public function reinitialiserMotDePasse(User $user)
    {
        $this->authorize('update', $user);

        $provisoire = Str::password(10, true, true, false);
        $user->update(['password' => $provisoire]);

        $this->audit->enregistrer('modification',
            "Mot de passe réinitialisé pour « {$user->name} »", $user, [], 'usagers');

        return back()->with('success', "Nouveau mot de passe provisoire : {$provisoire}");
    }

    /* ---------------------------------------------------------------------
     | Profil de l'utilisateur connecté
     |--------------------------------------------------------------------*/

    public function profile()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'current_password.required_with' => 'Saisissez votre mot de passe actuel pour le modifier.',
        ]);

        if (! empty($donnees['new_password'])) {
            if (! Hash::check($donnees['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
            $user->password = $donnees['new_password'];
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('utilisateurs', 'public');
        }

        $user->fill(collect($donnees)->only(['name', 'prenom', 'email', 'telephone', 'adresse'])->all());
        $user->save();

        return redirect()->route('profile')->with('success', 'Profil mis à jour.');
    }

    private function donneesFormulaire(): array
    {
        return [
            'roles' => User::ROLES,
            'statuts' => User::STATUTS,
            'rolesAdditionnels' => Role::orderBy('libelle')->get(),
            'anneesAcademiques' => AnneeAcademique::orderByDesc('date_debut')->get(),
        ];
    }
}
