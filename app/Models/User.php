<?php

namespace App\Models;

use App\Support\Parametres;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_BIBLIOTHECAIRE = 'bibliothecaire';

    public const ROLE_ENSEIGNANT = 'enseignant';

    public const ROLE_ETUDIANT = 'etudiant';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_BIBLIOTHECAIRE => 'Bibliothécaire',
        self::ROLE_ENSEIGNANT => 'Enseignant',
        self::ROLE_ETUDIANT => 'Étudiant',
    ];

    public const STATUTS = [
        'actif' => 'Actif',
        'suspendu' => 'Suspendu',
        'diplome' => 'Diplômé',
        'radie' => 'Radié',
    ];

    protected $fillable = [
        'name', 'prenom', 'email', 'password', 'role', 'matricule', 'telephone',
        'adresse', 'date_naissance', 'filiere', 'niveau', 'faculte', 'departement',
        'grade', 'photo', 'statut', 'annee_academique_id', 'nombre_emprunts', 'actif',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_naissance' => 'date',
            'actif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------*/

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function empruntsGeres()
    {
        return $this->hasMany(Emprunt::class, 'bibliothecaire_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function penalites()
    {
        return $this->hasMany(Penalite::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function anneeAcademique()
    {
        return $this->belongsTo(AnneeAcademique::class, 'annee_academique_id');
    }

    public function favoris()
    {
        return $this->belongsToMany(Livre::class, 'book_user_favorites', 'user_id', 'livre_id')->withTimestamps();
    }

    public function journaux()
    {
        return $this->hasMany(JournalActivite::class);
    }

    public function empruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en cours');
    }

    public function empruntsEnRetard()
    {
        return $this->emprunts()->where('statut', 'en retard');
    }

    public function penalitesBloquantes()
    {
        return $this->penalites()->bloquantes();
    }

    /* ---------------------------------------------------------------------
     | Rôles & permissions
     |--------------------------------------------------------------------*/

    public function estAdministrateur(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function estBibliothecaire(): bool
    {
        return $this->role === self::ROLE_BIBLIOTHECAIRE;
    }

    public function estEtudiant(): bool
    {
        return $this->role === self::ROLE_ETUDIANT;
    }

    public function estEnseignant(): bool
    {
        return $this->role === self::ROLE_ENSEIGNANT;
    }

    /** Personnel de la bibliothèque (admin ou bibliothécaire). */
    public function estPersonnel(): bool
    {
        return $this->estAdministrateur() || $this->estBibliothecaire();
    }

    /** Emprunteur : étudiant ou enseignant. */
    public function estEmprunteur(): bool
    {
        return $this->estEtudiant() || $this->estEnseignant();
    }

    public function aLeRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Permissions effectives : celles du rôle principal (users.role) plus
     * celles des rôles additionnels attribués. Mises en cache par rôle.
     */
    public function permissions(): array
    {
        $parRole = Cache::rememberForever('rbac.permissions_par_role', function () {
            return Role::with('permissions:id,nom')->get()
                ->mapWithKeys(fn (Role $role) => [$role->nom => $role->permissions->pluck('nom')->all()])
                ->all();
        });

        $noms = collect([$this->role])
            ->merge($this->relationLoaded('roles') ? $this->roles->pluck('nom') : [])
            ->unique();

        return $noms->flatMap(fn ($nom) => $parRole[$nom] ?? [])->unique()->values()->all();
    }

    public function peut(string $permission): bool
    {
        if ($this->estAdministrateur()) {
            return true; // l'administrateur a toutes les permissions
        }

        return in_array($permission, $this->permissions(), true);
    }

    /* ---------------------------------------------------------------------
     | Règles métier
     |--------------------------------------------------------------------*/

    public function estActif(): bool
    {
        return (bool) $this->actif && $this->statut === 'actif';
    }

    /** Quota d'emprunts simultanés, configurable par profil. */
    public function quotaEmprunts(): int
    {
        return match ($this->role) {
            self::ROLE_ENSEIGNANT => Parametres::entier('emprunt.max_enseignant', 10),
            self::ROLE_ETUDIANT => Parametres::entier('emprunt.max_etudiant', 3),
            default => Parametres::entier('emprunt.max_personnel', 5),
        };
    }

    /** Durée d'emprunt en jours, configurable par profil. */
    public function dureeEmprunt(): int
    {
        return match ($this->role) {
            self::ROLE_ENSEIGNANT => Parametres::entier('emprunt.duree_enseignant', 30),
            default => Parametres::entier('emprunt.duree_etudiant', 14),
        };
    }

    public function maxRenouvellements(): int
    {
        return match ($this->role) {
            self::ROLE_ENSEIGNANT => Parametres::entier('renouvellement.max_enseignant', 3),
            default => Parametres::entier('renouvellement.max_etudiant', 1),
        };
    }

    /**
     * Un utilisateur peut emprunter s'il est actif, sous son quota, sans retard
     * et sans pénalité bloquante au-delà du seuil configuré.
     */
    public function peutEmprunter(): bool
    {
        return empty($this->motifsBlocageEmprunt());
    }

    /**
     * @return array<int, string> Liste lisible des blocages (vide si tout va bien).
     */
    public function motifsBlocageEmprunt(): array
    {
        $motifs = [];

        if (! $this->estEmprunteur()) {
            $motifs[] = 'Seuls les étudiants et les enseignants peuvent emprunter.';
        }

        if (! $this->estActif()) {
            $motifs[] = "Ce compte est inactif ou suspendu ({$this->libelle_statut}).";
        }

        $enCours = $this->emprunts()->whereIn('statut', ['en cours', 'en retard'])->count();
        if ($enCours >= $this->quotaEmprunts()) {
            $motifs[] = "Cet utilisateur a atteint sa limite d'emprunts ({$this->quotaEmprunts()}).";
        }

        if ($this->emprunts()->where('statut', 'en retard')->exists()) {
            $motifs[] = 'Cet utilisateur possède au moins un emprunt en retard.';
        }

        $dette = (float) $this->penalitesBloquantes()->sum('montant')
            - (float) $this->penalitesBloquantes()->sum('montant_paye');
        $seuil = Parametres::decimal('penalite.seuil_blocage', 1000);
        if ($dette >= $seuil && $seuil > 0) {
            $motifs[] = 'Cet utilisateur possède une pénalité bloquante ('.number_format($dette, 0, ',', ' ').' FCFA).';
        }

        return $motifs;
    }

    /* ---------------------------------------------------------------------
     | Accesseurs & scopes
     |--------------------------------------------------------------------*/

    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom.' ' : '').$this->name);
    }

    public function getLibelleRoleAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? (string) $this->statut;
    }

    /** Photo de profil, ou avatar généré localement à partir des initiales. */
    public function getUrlPhotoAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/'.$this->photo);
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
            .'<rect width="128" height="128" rx="64" fill="%s"/>'
            .'<text x="64" y="64" font-family="Inter, Arial, sans-serif" font-size="48" font-weight="600" '
            .'fill="#ffffff" text-anchor="middle" dominant-baseline="central">%s</text></svg>',
            $this->couleurAvatar(),
            $this->initiales()
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** Deux premières initiales du nom. */
    public function initiales(): string
    {
        $mots = preg_split('/\s+/', trim($this->prenom.' '.$this->name)) ?: [];
        $initiales = collect($mots)
            ->filter()
            ->take(2)
            ->map(fn (string $mot) => mb_strtoupper(mb_substr($mot, 0, 1)))
            ->implode('');

        return $initiales ?: '?';
    }

    /** Couleur stable dérivée de l'identifiant, dans la palette de la marque. */
    private function couleurAvatar(): string
    {
        $palette = ['#123A7A', '#2563EB', '#0EA5E9', '#0F2557', '#3B82F6', '#1D4ED8'];

        return $palette[crc32((string) ($this->matricule ?: $this->email ?: $this->id)) % count($palette)];
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true)->where('statut', 'actif');
    }

    public function scopeRecherche($query, ?string $terme)
    {
        if (! $terme) {
            return $query;
        }

        return $query->where(function ($q) use ($terme) {
            $q->where('name', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%")
                ->orWhere('matricule', 'like', "%{$terme}%")
                ->orWhere('email', 'like', "%{$terme}%")
                ->orWhere('telephone', 'like', "%{$terme}%")
                ->orWhere('filiere', 'like', "%{$terme}%");
        });
    }
}
