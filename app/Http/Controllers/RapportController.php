<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\User;
use App\Services\StatistiqueService;
use App\Support\Export;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Rapports de gestion, exportables en PDF, Excel et CSV.
 */
class RapportController extends Controller
{
    /** Rapports disponibles : slug => libellé. */
    public const RAPPORTS = [
        'emprunts' => 'Rapport des emprunts',
        'retards' => 'Rapport des retards',
        'penalites' => 'Rapport des pénalités',
        'livres' => 'Rapport du catalogue',
        'usagers' => 'Rapport des usagers',
        'perdus' => 'Rapport des ouvrages perdus',
        'endommages' => 'Rapport des ouvrages endommagés',
    ];

    public function __construct(private readonly StatistiqueService $statistiques)
    {
        $this->middleware('can:rapports.generer');
    }

    public function index()
    {
        return view('rapports.index', [
            'rapports' => self::RAPPORTS,
            'stats' => $this->statistiques->indicateursGlobaux(),
        ]);
    }

    /**
     * Affiche (ou exporte) un rapport.
     * `?format=pdf|excel|csv` déclenche le téléchargement.
     */
    public function afficher(Request $request, string $rapport)
    {
        abort_unless(isset(self::RAPPORTS[$rapport]), 404);

        [$lignes, $colonnes, $statistiques] = $this->donnees($rapport, $request);

        $titre = self::RAPPORTS[$rapport];
        $periode = $this->libellePeriode($request);

        return match ($request->input('format')) {
            'pdf' => Pdf::loadView('rapports.pdf', compact('titre', 'periode', 'lignes', 'colonnes', 'statistiques'))
                ->setPaper('a4', 'landscape')
                ->download("rapport-{$rapport}-".now()->format('Y-m-d').'.pdf'),
            'excel' => Export::excel($lignes, $colonnes, "rapport-{$rapport}-".now()->format('Y-m-d'), $titre),
            'csv' => Export::csv($lignes, $colonnes, "rapport-{$rapport}-".now()->format('Y-m-d')),
            default => view('rapports.afficher', [
                'rapport' => $rapport,
                'titre' => $titre,
                'periode' => $periode,
                'lignes' => $lignes,
                'colonnes' => $colonnes,
                'statistiques' => $statistiques,
                'rapports' => self::RAPPORTS,
            ]),
        };
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: array<string,string>, 2: array<string,mixed>}
     */
    private function donnees(string $rapport, Request $request): array
    {
        $debut = $request->input('date_debut');
        $fin = $request->input('date_fin');

        return match ($rapport) {
            'emprunts' => $this->rapportEmprunts($request, $debut, $fin),
            'retards' => $this->rapportRetards(),
            'penalites' => $this->rapportPenalites($request, $debut, $fin),
            'livres' => $this->rapportLivres($request),
            'usagers' => $this->rapportUsagers($request),
            'perdus' => $this->rapportExemplaires(Exemplaire::STATUT_PERDU),
            'endommages' => $this->rapportExemplaires(Exemplaire::STATUT_ENDOMMAGE),
        };
    }

    private function rapportEmprunts(Request $request, ?string $debut, ?string $fin): array
    {
        $emprunts = Emprunt::with(['user:id,name,prenom,matricule', 'livre:id,titre,auteur', 'exemplaire:id,code_barre'])
            ->when($debut, fn ($q) => $q->whereDate('date_emprunt', '>=', $debut))
            ->when($fin, fn ($q) => $q->whereDate('date_emprunt', '<=', $fin))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_emprunt')
            ->get();

        return [
            $emprunts->map(fn (Emprunt $e) => [
                'id' => $e->id,
                'usager' => $e->user?->name,
                'matricule' => $e->user?->matricule,
                'ouvrage' => $e->livre?->titre,
                'exemplaire' => $e->exemplaire?->code_barre ?? '—',
                'date_emprunt' => $e->date_emprunt?->format('d/m/Y'),
                'echeance' => $e->date_retour_prevue?->format('d/m/Y'),
                'retour' => $e->date_retour_effective?->format('d/m/Y') ?? '—',
                'statut' => $e->libelle_statut,
            ]),
            [
                'id' => 'N°', 'usager' => 'Usager', 'matricule' => 'Matricule', 'ouvrage' => 'Ouvrage',
                'exemplaire' => 'Exemplaire', 'date_emprunt' => 'Emprunt', 'echeance' => 'Échéance',
                'retour' => 'Retour', 'statut' => 'Statut',
            ],
            [
                'Total' => $emprunts->count(),
                'En cours' => $emprunts->where('statut', Emprunt::STATUT_EN_COURS)->count(),
                'Retournés' => $emprunts->where('statut', Emprunt::STATUT_RETOURNE)->count(),
                'En retard' => $emprunts->where('statut', Emprunt::STATUT_EN_RETARD)->count(),
            ],
        ];
    }

    private function rapportRetards(): array
    {
        $retards = Emprunt::with(['user:id,name,prenom,matricule,email,telephone', 'livre:id,titre'])
            ->enRetard()->orderBy('date_retour_prevue')->get();

        return [
            $retards->map(fn (Emprunt $e) => [
                'usager' => $e->user?->name,
                'matricule' => $e->user?->matricule,
                'contact' => $e->user?->telephone ?: $e->user?->email,
                'ouvrage' => $e->livre?->titre,
                'echeance' => $e->date_retour_prevue?->format('d/m/Y'),
                'jours' => $e->joursRetard(),
                'penalite' => number_format($e->calculerPenaliteRetard(), 0, ',', ' '),
            ]),
            [
                'usager' => 'Usager', 'matricule' => 'Matricule', 'contact' => 'Contact',
                'ouvrage' => 'Ouvrage', 'echeance' => 'Échéance', 'jours' => 'Jours de retard',
                'penalite' => 'Pénalité estimée',
            ],
            [
                'Emprunts en retard' => $retards->count(),
                'Total des pénalités estimées' => number_format(
                    $retards->sum(fn (Emprunt $e) => $e->calculerPenaliteRetard()), 0, ',', ' '
                ),
            ],
        ];
    }

    private function rapportPenalites(Request $request, ?string $debut, ?string $fin): array
    {
        $penalites = Penalite::with(['user:id,name,prenom,matricule', 'emprunt.livre:id,titre'])
            ->when($debut, fn ($q) => $q->whereDate('created_at', '>=', $debut))
            ->when($fin, fn ($q) => $q->whereDate('created_at', '<=', $fin))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->latest()->get();

        return [
            $penalites->map(fn (Penalite $p) => [
                'date' => $p->created_at->format('d/m/Y'),
                'usager' => $p->user?->name,
                'matricule' => $p->user?->matricule,
                'type' => $p->libelle_type,
                'ouvrage' => $p->emprunt?->livre?->titre ?? '—',
                'montant' => number_format((float) $p->montant, 0, ',', ' '),
                'paye' => number_format((float) $p->montant_paye, 0, ',', ' '),
                'statut' => $p->libelle_statut,
            ]),
            [
                'date' => 'Date', 'usager' => 'Usager', 'matricule' => 'Matricule', 'type' => 'Type',
                'ouvrage' => 'Ouvrage', 'montant' => 'Montant', 'paye' => 'Payé', 'statut' => 'Statut',
            ],
            [
                'Nombre de pénalités' => $penalites->count(),
                'Total facturé' => number_format($penalites->where('statut', '!=', Penalite::STATUT_ANNULEE)->sum('montant'), 0, ',', ' '),
                'Total encaissé' => number_format($penalites->sum('montant_paye'), 0, ',', ' '),
                'Reste dû' => number_format($penalites->sum(fn (Penalite $p) => $p->estSoldee() ? 0 : $p->reste_a_payer), 0, ',', ' '),
            ],
        ];
    }

    private function rapportLivres(Request $request): array
    {
        $livres = Livre::withCount(['emprunts', 'exemplaires'])
            ->when($request->filled('categorie'), fn ($q) => $q->where('categorie', $request->categorie))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderBy('titre')->get();

        return [
            $livres->map(fn (Livre $l) => [
                'isbn' => $l->isbn,
                'titre' => $l->titre,
                'auteur' => $l->auteur,
                'categorie' => $l->categorie,
                'type' => $l->libelle_type,
                'exemplaires' => $l->exemplaires_count ?: $l->exemplaires_totaux,
                'disponibles' => $l->exemplaires_disponibles,
                'emprunts' => $l->emprunts_count,
                'rayon' => $l->emplacement_rayon,
            ]),
            [
                'isbn' => 'ISBN', 'titre' => 'Titre', 'auteur' => 'Auteur(s)', 'categorie' => 'Catégorie',
                'type' => 'Type', 'exemplaires' => 'Exemplaires', 'disponibles' => 'Disponibles',
                'emprunts' => 'Emprunts', 'rayon' => 'Rayon',
            ],
            [
                'Notices' => $livres->count(),
                'Exemplaires' => $livres->sum(fn ($l) => $l->exemplaires_count ?: $l->exemplaires_totaux),
                'Disponibles' => $livres->sum('exemplaires_disponibles'),
            ],
        ];
    }

    private function rapportUsagers(Request $request): array
    {
        $usagers = User::withCount(['emprunts', 'emprunts as emprunts_en_cours_count' => fn ($q) => $q->enCours()])
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->orderBy('name')->get();

        return [
            $usagers->map(fn (User $u) => [
                'matricule' => $u->matricule,
                'nom' => $u->name,
                'email' => $u->email,
                'role' => $u->libelle_role,
                'filiere' => $u->filiere ?? '—',
                'statut' => $u->libelle_statut,
                'emprunts' => $u->emprunts_count,
                'en_cours' => $u->emprunts_en_cours_count,
            ]),
            [
                'matricule' => 'Matricule', 'nom' => 'Nom', 'email' => 'Email', 'role' => 'Profil',
                'filiere' => 'Filière', 'statut' => 'Statut', 'emprunts' => 'Emprunts', 'en_cours' => 'En cours',
            ],
            [
                'Usagers' => $usagers->count(),
                'Actifs' => $usagers->where('statut', 'actif')->count(),
                'Emprunts en cours' => $usagers->sum('emprunts_en_cours_count'),
            ],
        ];
    }

    private function rapportExemplaires(string $statut): array
    {
        $exemplaires = Exemplaire::with(['livre:id,titre,auteur,isbn', 'emplacement.rayon'])
            ->where('statut', $statut)->orderBy('updated_at', 'desc')->get();

        return [
            $exemplaires->map(fn (Exemplaire $e) => [
                'code_barre' => $e->code_barre,
                'ouvrage' => $e->livre?->titre,
                'isbn' => $e->livre?->isbn,
                'etat' => Exemplaire::ETATS[$e->etat] ?? $e->etat,
                'emplacement' => $e->emplacement?->chemin_complet ?? '—',
                'prix' => $e->prix_achat ? number_format((float) $e->prix_achat, 0, ',', ' ') : '—',
                'date' => $e->updated_at->format('d/m/Y'),
            ]),
            [
                'code_barre' => 'Code-barres', 'ouvrage' => 'Ouvrage', 'isbn' => 'ISBN', 'etat' => 'État',
                'emplacement' => 'Emplacement', 'prix' => "Valeur d'achat", 'date' => 'Dernière mise à jour',
            ],
            [
                'Exemplaires concernés' => $exemplaires->count(),
                'Valeur totale' => number_format($exemplaires->sum('prix_achat'), 0, ',', ' '),
            ],
        ];
    }

    private function libellePeriode(Request $request): string
    {
        $debut = $request->input('date_debut');
        $fin = $request->input('date_fin');

        if ($debut && $fin) {
            return 'Du '.date('d/m/Y', strtotime($debut)).' au '.date('d/m/Y', strtotime($fin));
        }

        if ($debut) {
            return 'À partir du '.date('d/m/Y', strtotime($debut));
        }

        if ($fin) {
            return "Jusqu'au ".date('d/m/Y', strtotime($fin));
        }

        return 'Toutes périodes — édité le '.now()->format('d/m/Y à H:i');
    }
}
