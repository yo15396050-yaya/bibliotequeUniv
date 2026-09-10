<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocalisationRequest;
use App\Models\Bibliotheque;
use App\Models\Emplacement;
use App\Models\Rayon;
use App\Models\Salle;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * Gestion unifiée de la hiérarchie physique :
 * bibliothèques → salles → rayons → emplacements.
 */
class LocalisationController extends Controller
{
    private const MODELES = [
        'bibliotheques' => Bibliotheque::class,
        'salles' => Salle::class,
        'rayons' => Rayon::class,
        'emplacements' => Emplacement::class,
    ];

    private const LIBELLES = [
        'bibliotheques' => 'Bibliothèque',
        'salles' => 'Salle',
        'rayons' => 'Rayon',
        'emplacements' => 'Emplacement',
    ];

    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('can:localisations.gerer')->except('index');
    }

    public function index(Request $request)
    {
        $this->authorize('exemplaires.voir');

        return view('localisations.index', [
            'bibliotheques' => Bibliotheque::with('salles.rayons.emplacements')
                ->withCount('salles')->orderBy('nom')->get(),
            'nombreEmplacements' => Emplacement::count(),
            'niveauActif' => $request->input('niveau', 'bibliotheques'),
        ]);
    }

    public function store(StoreLocalisationRequest $request, string $niveau)
    {
        $modele = $this->modele($niveau);
        $entite = $modele::create($request->validated());

        $this->audit->creation($entite, self::LIBELLES[$niveau]." « {$this->nomDe($entite)} » créé(e)", 'localisations');

        return back()->with('success', self::LIBELLES[$niveau].' enregistré(e).');
    }

    public function update(StoreLocalisationRequest $request, string $niveau, int $id)
    {
        $modele = $this->modele($niveau);
        $entite = $modele::findOrFail($id);
        $entite->update($request->validated());

        $this->audit->modification($entite, self::LIBELLES[$niveau]." « {$this->nomDe($entite)} » modifié(e)", 'localisations');

        return back()->with('success', self::LIBELLES[$niveau].' mis(e) à jour.');
    }

    public function destroy(string $niveau, int $id)
    {
        $modele = $this->modele($niveau);
        $entite = $modele::findOrFail($id);

        if ($message = $this->motifBlocageSuppression($niveau, $entite)) {
            return back()->with('error', $message);
        }

        $this->audit->suppression($entite, self::LIBELLES[$niveau]." « {$this->nomDe($entite)} » supprimé(e)", 'localisations');
        $entite->delete();

        return back()->with('success', self::LIBELLES[$niveau].' supprimé(e).');
    }

    /** @return class-string */
    private function modele(string $niveau): string
    {
        abort_unless(isset(self::MODELES[$niveau]), 404, 'Niveau de localisation inconnu.');

        return self::MODELES[$niveau];
    }

    private function nomDe(object $entite): string
    {
        return $entite->nom ?? ('Étagère '.($entite->etagere ?? ''));
    }

    private function motifBlocageSuppression(string $niveau, object $entite): ?string
    {
        return match ($niveau) {
            'bibliotheques' => $entite->salles()->exists()
                ? 'Cette bibliothèque contient des salles : supprimez-les d\'abord.' : null,
            'salles' => $entite->rayons()->exists()
                ? 'Cette salle contient des rayons : supprimez-les d\'abord.' : null,
            'rayons' => $entite->emplacements()->exists()
                ? 'Ce rayon contient des emplacements : supprimez-les d\'abord.' : null,
            'emplacements' => $entite->exemplaires()->exists()
                ? 'Des exemplaires sont rangés à cet emplacement.' : null,
            default => null,
        };
    }
}
