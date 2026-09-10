<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Notice bibliographique. Les exemplaires physiques sont portés par
 * App\Models\Exemplaire ; `exemplaires_disponibles` / `exemplaires_totaux`
 * restent des compteurs dénormalisés, resynchronisés par
 * {@see Livre::synchroniserCompteurs()}.
 */
class Livre extends Model
{
    use HasFactory;

    public const TYPES_DOCUMENT = [
        'livre' => 'Livre',
        'memoire' => 'Mémoire',
        'these' => 'Thèse',
        'revue' => 'Revue',
        'article' => 'Article',
        'rapport' => 'Rapport',
        'manuel' => 'Manuel',
        'numerique' => 'Document numérique',
        'autre' => 'Autre',
    ];

    public const NIVEAUX_ACADEMIQUES = [
        'licence1' => 'Licence 1',
        'licence2' => 'Licence 2',
        'licence3' => 'Licence 3',
        'master' => 'Master',
        'doctorat' => 'Doctorat',
        'tous' => 'Tous niveaux',
    ];

    protected $fillable = [
        'isbn', 'titre', 'sous_titre', 'auteur', 'editeur', 'editeur_id',
        'annee_publication', 'edition', 'categorie', 'categorie_id', 'type_document',
        'niveau_academique', 'domaine', 'langue', 'nombre_pages', 'resume', 'description',
        'mots_cles', 'emplacement_rayon', 'emplacement_id', 'image_couverture',
        'exemplaires_disponibles', 'exemplaires_totaux', 'statut',
        'fichier_numerique', 'disponible_numerique', 'extension_numerique',
        'autoriser_telechargement', 'taille_fichier',
    ];

    protected $casts = [
        'annee_publication' => 'integer',
        'nombre_pages' => 'integer',
        'exemplaires_disponibles' => 'integer',
        'exemplaires_totaux' => 'integer',
        'disponible_numerique' => 'boolean',
        'autoriser_telechargement' => 'boolean',
    ];

    /* ---------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------*/

    public function exemplaires()
    {
        return $this->hasMany(Exemplaire::class);
    }

    public function exemplairesDisponibles()
    {
        return $this->exemplaires()->where('statut', Exemplaire::STATUT_DISPONIBLE);
    }

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationsActives()
    {
        return $this->reservations()->where('statut', 'active')->orderBy('position_file_attente');
    }

    public function auteurs()
    {
        return $this->belongsToMany(Auteur::class, 'auteur_livre')->withPivot('role');
    }

    public function categorieRef()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function editeurRef()
    {
        return $this->belongsTo(Editeur::class, 'editeur_id');
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function documents()
    {
        return $this->hasMany(DocumentNumerique::class);
    }

    /* ---------------------------------------------------------------------
     | État & disponibilité
     |--------------------------------------------------------------------*/

    public function estDisponible(): bool
    {
        return $this->exemplaires_disponibles > 0
            && ! in_array($this->statut, ['perdu', 'en réparation'], true);
    }

    public function estDisponibleNumerique(): bool
    {
        return (bool) $this->disponible_numerique && ! empty($this->fichier_numerique);
    }

    public function nombreEmpruntsEnCours(): int
    {
        return $this->emprunts()->whereIn('statut', ['en cours', 'en retard'])->count();
    }

    /**
     * Recalcule les compteurs à partir des exemplaires réels.
     * Repli sur les compteurs saisis à la main tant qu'aucun exemplaire n'existe.
     */
    public function synchroniserCompteurs(): void
    {
        $total = $this->exemplaires()->count();

        if ($total === 0) {
            $enCours = $this->nombreEmpruntsEnCours();
            $this->forceFill([
                'exemplaires_disponibles' => max(0, (int) $this->exemplaires_totaux - $enCours),
            ])->save();

            return;
        }

        $disponibles = $this->exemplaires()->where('statut', Exemplaire::STATUT_DISPONIBLE)->count();
        $horsCirculation = $this->exemplaires()
            ->whereIn('statut', [Exemplaire::STATUT_RETIRE, Exemplaire::STATUT_PERDU])
            ->count();

        $this->forceFill([
            'exemplaires_totaux' => $total,
            'exemplaires_disponibles' => $disponibles,
            'statut' => $disponibles > 0
                ? 'disponible'
                : ($total > $horsCirculation ? 'emprunté' : 'perdu'),
        ])->save();
    }

    /* ---------------------------------------------------------------------
     | Accesseurs
     |--------------------------------------------------------------------*/

    public function getLibelleTypeAttribute(): string
    {
        return self::TYPES_DOCUMENT[$this->type_document] ?? 'Livre';
    }

    /** Auteurs normalisés si présents, sinon le champ texte historique. */
    public function getAuteursAffichesAttribute(): string
    {
        if ($this->relationLoaded('auteurs') && $this->auteurs->isNotEmpty()) {
            return $this->auteurs->map->nom_complet->implode(', ');
        }

        return (string) $this->auteur;
    }

    public function getUrlCouvertureAttribute(): ?string
    {
        return $this->image_couverture ? asset('storage/'.$this->image_couverture) : null;
    }

    public function getPopulariteAttribute(): float
    {
        $totalEmprunts = $this->emprunts()->count();
        $jours = max(1, (int) abs(now()->diffInDays($this->created_at)));

        return round($totalEmprunts / $jours, 2);
    }

    public function getUrlLectureAttribute(): ?string
    {
        return $this->estDisponibleNumerique() ? route('livres.read', $this->id) : null;
    }

    public function getUrlTelechargementAttribute(): ?string
    {
        return $this->estDisponibleNumerique() && $this->autoriser_telechargement
            ? route('livres.download', $this->id)
            : null;
    }

    /* ---------------------------------------------------------------------
     | Scopes de recherche
     |--------------------------------------------------------------------*/

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('exemplaires_disponibles', '>', 0);
    }

    /** Recherche plein texte sur une seule barre de recherche. */
    public function scopeRecherche(Builder $query, ?string $terme): Builder
    {
        if (! $terme = trim((string) $terme)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($terme) {
            $q->where('titre', 'like', "%{$terme}%")
                ->orWhere('sous_titre', 'like', "%{$terme}%")
                ->orWhere('auteur', 'like', "%{$terme}%")
                ->orWhere('isbn', 'like', "%{$terme}%")
                ->orWhere('editeur', 'like', "%{$terme}%")
                ->orWhere('categorie', 'like', "%{$terme}%")
                ->orWhere('mots_cles', 'like', "%{$terme}%")
                ->orWhere('domaine', 'like', "%{$terme}%")
                ->orWhereHas('auteurs', fn (Builder $a) => $a->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%"))
                ->orWhereHas('exemplaires', fn (Builder $e) => $e->where('code_barre', $terme));
        });
    }

    /**
     * Applique les filtres avancés du catalogue.
     *
     * @param  array<string, mixed>  $filtres
     */
    public function scopeFiltres(Builder $query, array $filtres): Builder
    {
        return $query
            ->when($filtres['categorie'] ?? null, fn ($q, $v) => $q->where('categorie', $v))
            ->when($filtres['categorie_id'] ?? null, fn ($q, $v) => $q->where('categorie_id', $v))
            ->when($filtres['langue'] ?? null, fn ($q, $v) => $q->where('langue', $v))
            ->when($filtres['type'] ?? null, fn ($q, $v) => $q->where('type_document', $v))
            ->when($filtres['annee'] ?? null, fn ($q, $v) => $q->where('annee_publication', $v))
            ->when($filtres['niveau'] ?? null, fn ($q, $v) => $q->where('niveau_academique', $v))
            ->when($filtres['statut'] ?? null, fn ($q, $v) => $q->where('statut', $v))
            ->when($filtres['emplacement'] ?? null, fn ($q, $v) => $q->where('emplacement_rayon', 'like', "%{$v}%"))
            ->when(($filtres['disponible'] ?? null) === '1', fn ($q) => $q->disponibles())
            ->when(($filtres['numerique'] ?? null) === '1', fn ($q) => $q->where('disponible_numerique', true));
    }
}
