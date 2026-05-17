<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LivreController extends Controller
{
    public function index(Request $request)
    {
        $query = Livre::query();

        // Recherche
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', "%{$request->search}%")
                    ->orWhere('auteur', 'like', "%{$request->search}%")
                    ->orWhere('isbn', 'like', "%{$request->search}%")
                    ->orWhere('categorie', 'like', "%{$request->search}%");
            });
        }

        // Filtrage par catégorie
        if ($request->has('categorie') && !empty($request->categorie)) {
            $query->where('categorie', $request->categorie);
        }

        // Filtrage par statut
        if ($request->has('statut') && !empty($request->statut)) {
            $query->where('statut', $request->statut);
        }

        $livres = $query->orderBy('titre')->paginate(15);
        $categories = Livre::select('categorie')->distinct()->pluck('categorie');

        if ($request->ajax()) {
            return view('livres.partials._table', compact('livres'))->render();
        }

        return view('livres.index', compact('livres', 'categories'));
    }

    public function create()
    {
        return view('livres.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'isbn' => 'required|unique:livres|max:20',
                'titre' => 'required|max:255',
                'auteur' => 'required|max:255',
                'editeur' => 'required|max:255',
                'annee_publication' => 'required|integer|min:1900|max:' . date('Y'),
                'categorie' => 'required|max:100',
                'langue' => 'required|max:50',
                'nombre_pages' => 'required|integer|min:1',
                'resume' => 'nullable',
                'emplacement_rayon' => 'required|max:50',
                'image_couverture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'fichier_numerique' => 'nullable|file|mimes:pdf|max:10240',
                'exemplaires_totaux' => 'required|integer|min:1',
            ]);

            // Gestion de l'image de couverture
            if ($request->hasFile('image_couverture')) {
                $path = $request->file('image_couverture')->store('livres', 'public');
                $validated['image_couverture'] = $path;
            }

            // Gestion du fichier numérique PDF
            if ($request->hasFile('fichier_numerique')) {
                $path = $request->file('fichier_numerique')->store('livres/numeriques', 'public');
                $validated['fichier_numerique'] = $path;
                $validated['disponible_numerique'] = true;
            } else {
                $validated['disponible_numerique'] = false;
            }

            $validated['exemplaires_disponibles'] = $validated['exemplaires_totaux'];
            $validated['statut'] = 'disponible';

            Livre::create($validated);

            return redirect()->route('livres.index')
                ->with('success', 'Livre ajouté avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Afficher les erreurs de validation pour le débogage
            if (app()->environment('local')) {
                dd($e->errors());
            }
            throw $e;
        }
    }

    public function show(Livre $livre)
    {
        $livre->load([
            'emprunts.user' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'reservations' => function ($query) {
                $query->where('statut', 'active');
            }
        ]);

        // Suggestions de livres similaires (même catégorie, exclure le livre actuel)
        $similaires = Livre::where('categorie', $livre->categorie)
            ->where('id', '!=', $livre->id)
            ->where('statut', 'disponible')
            ->take(4)
            ->get();

        return view('livres.show', compact('livre', 'similaires'));
    }

    public function edit(Livre $livre)
    {
        return view('livres.edit', compact('livre'));
    }

    public function update(Request $request, Livre $livre)
    {
        $validated = $request->validate([
            'isbn' => 'required|max:20|unique:livres,isbn,' . $livre->id,
            'titre' => 'required|max:255',
            'auteur' => 'required|max:255',
            'editeur' => 'required|max:255',
            'annee_publication' => 'required|integer|min:1900|max:' . date('Y'),
            'categorie' => 'required|max:100',
            'langue' => 'required|max:50',
            'nombre_pages' => 'required|integer|min:1',
            'resume' => 'nullable',
            'emplacement_rayon' => 'required|max:50',
            'image_couverture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'exemplaires_totaux' => 'required|integer|min:1',
            'statut' => 'required|in:disponible,emprunté,réservé,perdu,en réparation',
        ]);

        // Gestion de l'image de couverture
        if ($request->hasFile('image_couverture')) {
            // Supprimer l'ancienne image si elle existe
            if ($livre->image_couverture) {
                Storage::disk('public')->delete($livre->image_couverture);
            }

            $path = $request->file('image_couverture')->store('livres', 'public');
            $validated['image_couverture'] = $path;
        }

        // Calcul des exemplaires disponibles
        $empruntsEnCours = $livre->emprunts()->where('statut', 'en cours')->count();
        $validated['exemplaires_disponibles'] = max(0, $validated['exemplaires_totaux'] - $empruntsEnCours);

        $livre->update($validated);

        return redirect()->route('livres.show', $livre)
            ->with('success', 'Livre mis à jour avec succès.');
    }

    public function destroy(Livre $livre)
    {
        // Vérifier si le livre peut être supprimé
        if ($livre->emprunts()->where('statut', 'en cours')->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer le livre : il a des exemplaires empruntés.');
        }

        // Supprimer l'image si elle existe
        if ($livre->image_couverture) {
            Storage::disk('public')->delete($livre->image_couverture);
        }

        $livre->delete();

        return redirect()->route('livres.index')
            ->with('success', 'Livre supprimé avec succès.');
    }

    public function catalogue(Request $request)
    {
        $query = Livre::query();

        // Recherche
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', "%{$request->search}%")
                    ->orWhere('auteur', 'like', "%{$request->search}%")
                    ->orWhere('isbn', 'like', "%{$request->search}%");
            });
        }

        // Filtrage par catégorie
        if ($request->has('categorie') && !empty($request->categorie)) {
            $query->where('categorie', $request->categorie);
        }

        // Filtrage par disponibilité
        if ($request->has('disponible') && $request->disponible == '1') {
            $query->where('exemplaires_disponibles', '>', 0);
        }

        $livres = $query->orderBy('titre')->paginate(20);
        $categories = Livre::distinct()->pluck('categorie')->filter()->sort();

        if ($request->ajax()) {
            return view('livres.partials._catalogue_grid', compact('livres'))->render();
        }

        return view('livres.catalogue', compact('livres', 'categories'));
    }

    public function generateQRCode(Livre $livre)
    {
        // Générer le QR Code au format SVG et le transformer en base64 pour un affichage garanti
        $qrCodeData = QrCode::size(250)
            ->format('svg')
            ->color(93, 64, 55) // Couleur Bois Sombre (#5D4037)
            ->generate(route('livres.show', $livre->id));

        $qrCode = 'data:image/svg+xml;base64,' . base64_encode($qrCodeData);

        return view('livres.qrcode', compact('livre', 'qrCode'));
    }

    public function lire(Livre $livre)
    {
        // Vérifier si le livre est disponible en version numérique
        if (!$livre->estDisponibleNumerique()) {
            return back()->with('error', 'Ce livre n\'est pas disponible en lecture en ligne.');
        }

        return view('livres.lire', compact('livre'));
    }
}