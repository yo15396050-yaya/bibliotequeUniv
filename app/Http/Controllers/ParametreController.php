<?php

namespace App\Http\Controllers;

use App\Models\AnneeAcademique;
use App\Models\Role;
use App\Services\AuditService;
use App\Support\Parametres;
use App\Support\Permissions;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('can:parametres.gerer');
    }

    public function index(Request $request)
    {
        $groupe = $request->input('groupe', 'general');

        $definitions = collect(Parametres::DEFAUTS)
            ->filter(fn ($meta) => $meta['groupe'] === $groupe);

        return view('settings.index', [
            'groupe' => $groupe,
            'groupes' => collect(Parametres::DEFAUTS)->pluck('groupe')->unique()->values(),
            'definitions' => $definitions,
            'valeurs' => Parametres::toutes(),
        ]);
    }

    public function update(Request $request)
    {
        $groupe = $request->input('groupe', 'general');

        $definitions = collect(Parametres::DEFAUTS)->filter(fn ($m) => $m['groupe'] === $groupe);

        $regles = [];
        foreach ($definitions as $cle => $meta) {
            $champ = 'parametres.'.str_replace('.', '__', $cle);
            $regles[$champ] = match ($meta['type']) {
                'integer' => ['nullable', 'integer', 'min:0', 'max:100000'],
                'decimal' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
                'boolean' => ['nullable', 'boolean'],
                default => ['nullable', 'string', 'max:500'],
            };
        }

        $request->validate($regles);
        $soumis = $request->input('parametres', []);

        foreach ($definitions as $cle => $meta) {
            $champ = str_replace('.', '__', $cle);

            $valeur = $meta['type'] === 'boolean'
                ? $request->boolean('parametres.'.$champ)
                : ($soumis[$champ] ?? null);

            if ($meta['type'] !== 'boolean' && ($valeur === null || $valeur === '')) {
                continue;
            }

            Parametres::definir($cle, $valeur);
        }

        $this->audit->enregistrer('modification',
            "Paramètres du groupe « {$groupe} » mis à jour", null, ['groupe' => $groupe], 'administration');

        return back()->with('success', 'Paramètres enregistrés.');
    }

    /** Écran des rôles et permissions. */
    public function roles()
    {
        return view('settings.roles', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('libelle')->get(),
            'catalogue' => Permissions::CATALOGUE,
        ]);
    }

    public function updateRole(Request $request, Role $role)
    {
        $donnees = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,nom'],
        ]);

        $ids = \App\Models\Permission::whereIn('nom', $donnees['permissions'] ?? [])->pluck('id');
        $role->permissions()->sync($ids);
        $role->touch(); // vide le cache RBAC

        $this->audit->enregistrer('permission',
            "Permissions du rôle « {$role->libelle} » modifiées", $role,
            ['permissions' => $donnees['permissions'] ?? []], 'administration');

        return back()->with('success', "Permissions du rôle « {$role->libelle} » mises à jour.");
    }

    /** Années académiques. */
    public function anneesAcademiques()
    {
        return view('settings.annees', [
            'annees' => AnneeAcademique::withCount('users')->orderByDesc('date_debut')->get(),
        ]);
    }

    public function storeAnnee(Request $request)
    {
        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:20', 'unique:annees_academiques,libelle'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'active' => ['nullable', 'boolean'],
        ]);

        $donnees['active'] = $request->boolean('active');

        if ($donnees['active']) {
            AnneeAcademique::query()->update(['active' => false]);
        }

        AnneeAcademique::create($donnees);

        return back()->with('success', 'Année académique enregistrée.');
    }

    public function activerAnnee(AnneeAcademique $annee)
    {
        AnneeAcademique::query()->update(['active' => false]);
        $annee->update(['active' => true]);

        return back()->with('success', "Année académique « {$annee->libelle} » activée.");
    }

    /** Vide les caches applicatifs. */
    public function viderCache()
    {
        Parametres::viderCache();
        \Illuminate\Support\Facades\Cache::forget('rbac.permissions_par_role');

        return back()->with('success', 'Caches applicatifs vidés.');
    }
}
