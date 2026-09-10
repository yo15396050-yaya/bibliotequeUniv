<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\DocumentNumerique;
use App\Models\Livre;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Accès aux documents numériques.
 *
 * Sécurité : les fichiers vivent sur un disque privé et chaque accès est
 * autorisé par DocumentNumeriquePolicy. Modifier l'identifiant dans l'URL
 * ne donne accès à rien de plus.
 */
class DigitalBookController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    /** Liseuse en ligne du document de référence d'un ouvrage. */
    public function read(Livre $livre)
    {
        $this->authorize('view', $livre);

        $document = $this->documentDeReference($livre);

        if (! $document) {
            return back()->with('error', "Cet ouvrage n'a pas de version numérique consultable.");
        }

        $this->authorize('view', $document);

        return view('livres.lire', compact('livre', 'document'));
    }

    /** Flux du fichier pour la liseuse (support des requêtes Range). */
    public function stream(Livre $livre): StreamedResponse
    {
        $document = $this->documentDeReference($livre);
        abort_unless($document, 404, 'Aucun document numérique pour cet ouvrage.');
        $this->authorize('view', $document);

        return $this->flux($document, 'inline');
    }

    public function download(Livre $livre)
    {
        $document = $this->documentDeReference($livre);
        abort_unless($document, 404, 'Aucun document numérique pour cet ouvrage.');
        $this->authorize('download', $document);

        $this->documents->enregistrerTelechargement($document);

        return $this->flux($document, 'attachment');
    }

    /** Consultation d'un document précis (ouvrage à plusieurs fichiers). */
    public function afficherDocument(DocumentNumerique $document): StreamedResponse
    {
        $this->authorize('view', $document);

        return $this->flux($document, 'inline');
    }

    public function telechargerDocument(DocumentNumerique $document): StreamedResponse
    {
        $this->authorize('download', $document);

        $this->documents->enregistrerTelechargement($document);

        return $this->flux($document, 'attachment');
    }

    public function store(StoreDocumentRequest $request, Livre $livre)
    {
        $document = $this->documents->ajouter($livre, $request->file('fichier'), $request->validated());

        return back()->with('success', "Document « {$document->titre} » ajouté.");
    }

    public function destroy(DocumentNumerique $document)
    {
        $this->authorize('delete', $document);

        $titre = $document->titre;
        $this->documents->supprimer($document);

        return back()->with('success', "Document « {$titre} » supprimé.");
    }

    /* ------------------------------------------------------------------ */

    private function documentDeReference(Livre $livre): ?DocumentNumerique
    {
        $document = $livre->documents()->first();

        if ($document) {
            return $document;
        }

        // Repli sur l'ancien champ `fichier_numerique` (avant la table dédiée).
        if (! $livre->estDisponibleNumerique()) {
            return null;
        }

        return new DocumentNumerique([
            'livre_id' => $livre->id,
            'titre' => $livre->titre,
            'chemin' => $livre->fichier_numerique,
            'nom_original' => basename($livre->fichier_numerique),
            'format' => $livre->extension_numerique ?: pathinfo($livre->fichier_numerique, PATHINFO_EXTENSION),
            'mime_type' => 'application/pdf',
            'taille' => (int) $livre->taille_fichier,
            'visibilite' => 'authentifie',
            'autoriser_telechargement' => (bool) $livre->autoriser_telechargement,
        ]);
    }

    private function flux(DocumentNumerique $document, string $disposition): StreamedResponse
    {
        $disque = Storage::disk(DocumentService::DISQUE);

        abort_unless($disque->exists($document->chemin), 404, 'Fichier introuvable sur le serveur.');

        $nom = \Illuminate\Support\Str::slug($document->titre).'.'.$document->format;

        return $disque->response($document->chemin, $nom, [
            'Content-Type' => $document->mime_type ?: $disque->mimeType($document->chemin),
            'Content-Disposition' => $disposition.'; filename="'.$nom.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
