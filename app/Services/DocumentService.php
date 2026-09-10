<?php

namespace App\Services;

use App\Exceptions\RegleMetierException;
use App\Models\DocumentNumerique;
use App\Models\Livre;
use App\Support\Parametres;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gestion des fichiers numériques.
 *
 * Les fichiers sont stockés sur le disque privé `private_books` (hors du
 * répertoire public) : impossible d'y accéder en devinant une URL.
 */
class DocumentService
{
    public const DISQUE = 'private_books';

    public function __construct(private readonly AuditService $audit) {}

    /** @return array<int, string> extensions autorisées */
    public static function formatsAutorises(): array
    {
        return collect(explode(',', Parametres::chaine('document.formats_autorises', 'pdf,epub,docx')))
            ->map(fn ($f) => strtolower(trim($f)))
            ->filter()
            ->values()
            ->all();
    }

    public static function tailleMaxKo(): int
    {
        return Parametres::entier('document.taille_max_mo', 50) * 1024;
    }

    public function ajouter(Livre $livre, UploadedFile $fichier, array $donnees = []): DocumentNumerique
    {
        $extension = strtolower($fichier->getClientOriginalExtension());

        if (! in_array($extension, self::formatsAutorises(), true)) {
            throw new RegleMetierException(
                'Format de fichier non autorisé. Formats acceptés : '.implode(', ', self::formatsAutorises()).'.'
            );
        }

        $nom = Str::slug(pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME));
        $chemin = $fichier->storeAs(
            'livres/'.$livre->id,
            $nom.'-'.Str::random(8).'.'.$extension,
            self::DISQUE
        );

        $document = DocumentNumerique::create([
            'livre_id' => $livre->id,
            'titre' => $donnees['titre'] ?? $livre->titre,
            'chemin' => $chemin,
            'nom_original' => $fichier->getClientOriginalName(),
            'format' => $extension,
            'mime_type' => $fichier->getClientMimeType(),
            'taille' => $fichier->getSize(),
            'visibilite' => $donnees['visibilite'] ?? 'authentifie',
            'autoriser_telechargement' => (bool) ($donnees['autoriser_telechargement'] ?? true),
            'ajoute_par' => Auth::id(),
        ]);

        // Le premier document devient la version numérique de référence de la notice.
        if (! $livre->estDisponibleNumerique()) {
            $livre->forceFill([
                'fichier_numerique' => $chemin,
                'disponible_numerique' => true,
                'extension_numerique' => $extension,
                'autoriser_telechargement' => $document->autoriser_telechargement,
                'taille_fichier' => $document->taille,
            ])->save();
        }

        $this->audit->creation($document, "Document « {$document->titre} » ajouté à « {$livre->titre} »", 'documents');

        return $document;
    }

    public function supprimer(DocumentNumerique $document): void
    {
        $livre = $document->livre;

        if (Storage::disk(self::DISQUE)->exists($document->chemin)) {
            Storage::disk(self::DISQUE)->delete($document->chemin);
        }

        $this->audit->suppression($document, "Document « {$document->titre} » supprimé", 'documents');
        $cheminSupprime = $document->chemin;
        $document->delete();

        if ($livre && $livre->fichier_numerique === $cheminSupprime) {
            $remplacant = $livre->documents()->first();

            $livre->forceFill([
                'fichier_numerique' => $remplacant?->chemin,
                'disponible_numerique' => (bool) $remplacant,
                'extension_numerique' => $remplacant?->format,
                'taille_fichier' => $remplacant?->taille,
            ])->save();
        }
    }

    public function existe(DocumentNumerique $document): bool
    {
        return Storage::disk(self::DISQUE)->exists($document->chemin);
    }

    public function enregistrerTelechargement(DocumentNumerique $document): void
    {
        $document->increment('nombre_telechargements');

        $this->audit->enregistrer(
            'telechargement',
            "Téléchargement du document « {$document->titre} »",
            $document,
            ['format' => $document->format],
            'documents'
        );
    }
}
