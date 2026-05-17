<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Livre;
use Illuminate\Support\Facades\Storage;

class DigitalBookController extends Controller
{
    /**
     * Read the book (streaming for PDF)
     */
    public function stream(Livre $livre)
    {
        if (!$livre->estDisponibleNumerique()) {
            abort(404, 'Fichier numérique introuvable.');
        }

        if (!auth()->check()) {
            abort(403, 'Accès refusé.');
        }

        $path = $livre->fichier_numerique;
        if (!Storage::disk('private_books')->exists($path)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        // Serve file for proper Range request support (ideal for PDF)
        return Storage::disk('private_books')->response($path);
    }

    /**
     * Download the book
     */
    public function download(Livre $livre)
    {
        if (!$livre->estDisponibleNumerique() || !$livre->autoriser_telechargement) {
            abort(403, 'Téléchargement non autorisé.');
        }

        if (!auth()->check()) {
            abort(403, 'Accès refusé.');
        }

        $path = $livre->fichier_numerique;
        if (!Storage::disk('private_books')->exists($path)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        $filename = \Str::slug($livre->titre) . '.' . pathinfo($path, PATHINFO_EXTENSION);
        return Storage::disk('private_books')->download($path, $filename);
    }
}
