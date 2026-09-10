<?php

namespace Tests\Feature;

use App\Exceptions\RegleMetierException;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Services\EmpruntService;
use App\Support\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Prêt, retour, calcul du retard et de la pénalité.
 */
class CirculationTest extends TestCase
{
    use RefreshDatabase;

    private EmpruntService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        $this->service = app(EmpruntService::class);
        Notification::fake();
    }

    private function livreAvecExemplaires(int $quantite = 2): Livre
    {
        $livre = Livre::factory()->create(['exemplaires_totaux' => $quantite]);
        Exemplaire::factory()->count($quantite)->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        return $livre->fresh();
    }

    public function test_un_emprunt_est_enregistre_et_l_exemplaire_passe_en_emprunte(): void
    {
        $etudiant = $this->etudiant();
        $livre = $this->livreAvecExemplaires(2);

        $emprunt = $this->service->enregistrerEmprunt($etudiant, $livre);

        $this->assertSame(Emprunt::STATUT_EN_COURS, $emprunt->statut);
        $this->assertNotNull($emprunt->exemplaire_id);
        $this->assertSame(Exemplaire::STATUT_EMPRUNTE, $emprunt->exemplaire->statut);
        $this->assertSame(1, $livre->fresh()->exemplaires_disponibles);

        // L'échéance suit la durée configurée pour le profil.
        $this->assertSame(
            now()->addDays(Parametres::entier('emprunt.duree_etudiant'))->toDateString(),
            $emprunt->date_retour_prevue->toDateString()
        );
    }

    public function test_l_enseignant_beneficie_d_une_duree_et_d_un_quota_etendus(): void
    {
        $enseignant = $this->enseignant();

        $this->assertSame(Parametres::entier('emprunt.duree_enseignant'), $enseignant->dureeEmprunt());
        $this->assertSame(Parametres::entier('emprunt.max_enseignant'), $enseignant->quotaEmprunts());

        $emprunt = $this->service->enregistrerEmprunt($enseignant, $this->livreAvecExemplaires());

        $this->assertSame(
            now()->addDays(Parametres::entier('emprunt.duree_enseignant'))->toDateString(),
            $emprunt->date_retour_prevue->toDateString()
        );
    }

    public function test_un_exemplaire_deja_emprunte_est_refuse(): void
    {
        $livre = $this->livreAvecExemplaires(1);
        $exemplaire = $livre->exemplaires()->first();

        $this->service->enregistrerEmprunt($this->etudiant(), $livre, $exemplaire);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('Cet exemplaire est déjà emprunté.');

        $this->service->enregistrerEmprunt($this->etudiant(), $livre, $exemplaire->fresh());
    }

    public function test_le_quota_d_emprunts_est_respecte(): void
    {
        $etudiant = $this->etudiant();
        $quota = $etudiant->quotaEmprunts();

        for ($i = 0; $i < $quota; $i++) {
            $this->service->enregistrerEmprunt($etudiant, $this->livreAvecExemplaires(1));
        }

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage("limite d'emprunts");

        $this->service->enregistrerEmprunt($etudiant->fresh(), $this->livreAvecExemplaires(1));
    }

    public function test_un_usager_en_retard_ne_peut_plus_emprunter(): void
    {
        $etudiant = $this->etudiant();
        $livre = $this->livreAvecExemplaires(1);

        Emprunt::create([
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'date_emprunt' => now()->subDays(30)->toDateString(),
            'date_retour_prevue' => now()->subDays(10)->toDateString(),
            'statut' => Emprunt::STATUT_EN_RETARD,
        ]);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('en retard');

        $this->service->enregistrerEmprunt($etudiant->fresh(), $this->livreAvecExemplaires(1));
    }

    public function test_une_penalite_bloquante_empeche_l_emprunt(): void
    {
        $etudiant = $this->etudiant();

        Penalite::create([
            'user_id' => $etudiant->id,
            'type' => Penalite::TYPE_PERTE,
            'montant' => Parametres::decimal('penalite.seuil_blocage') + 5000,
            'statut' => Penalite::STATUT_IMPAYEE,
            'motif' => 'Perte d\'ouvrage',
        ]);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('pénalité bloquante');

        $this->service->enregistrerEmprunt($etudiant->fresh(), $this->livreAvecExemplaires());
    }

    public function test_un_compte_suspendu_ne_peut_pas_emprunter(): void
    {
        $etudiant = $this->etudiant(['statut' => 'suspendu']);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('inactif ou suspendu');

        $this->service->enregistrerEmprunt($etudiant, $this->livreAvecExemplaires());
    }

    public function test_un_retour_dans_les_delais_ne_genere_aucune_penalite(): void
    {
        $livre = $this->livreAvecExemplaires(1);
        $emprunt = $this->service->enregistrerEmprunt($this->etudiant(), $livre);

        $emprunt = $this->service->enregistrerRetour($emprunt, 'bon');

        $this->assertSame(Emprunt::STATUT_RETOURNE, $emprunt->statut);
        $this->assertNotNull($emprunt->date_retour_effective);
        $this->assertSame(0, Penalite::count());

        // L'exemplaire est immédiatement remis en circulation.
        $this->assertSame(Exemplaire::STATUT_DISPONIBLE, $emprunt->exemplaire->statut);
        $this->assertSame(1, $livre->fresh()->exemplaires_disponibles);
    }

    public function test_le_retard_et_la_penalite_sont_calcules_correctement(): void
    {
        $etudiant = $this->etudiant();
        $livre = $this->livreAvecExemplaires(1);
        $exemplaire = $livre->exemplaires()->first();

        $emprunt = Emprunt::create([
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'exemplaire_id' => $exemplaire->id,
            'date_emprunt' => now()->subDays(20)->toDateString(),
            'date_retour_prevue' => now()->subDays(5)->toDateString(),
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);
        $exemplaire->update(['statut' => Exemplaire::STATUT_EMPRUNTE]);

        $this->assertSame(5, $emprunt->joursRetard());
        $this->assertTrue($emprunt->estEnRetard());

        $montantAttendu = 5 * Parametres::decimal('penalite.montant_par_jour');
        $this->assertSame($montantAttendu, $emprunt->calculerPenaliteRetard());

        $this->service->enregistrerRetour($emprunt, 'bon');

        $penalite = Penalite::first();
        $this->assertNotNull($penalite);
        $this->assertSame(Penalite::TYPE_RETARD, $penalite->type);
        $this->assertEquals($montantAttendu, (float) $penalite->montant);
        $this->assertSame(5, $penalite->jours_retard);
        $this->assertSame(Penalite::STATUT_IMPAYEE, $penalite->statut);
    }

    public function test_la_penalite_de_retard_respecte_le_plafond_configure(): void
    {
        Parametres::definir('penalite.plafond_retard', 300);

        $emprunt = Emprunt::create([
            'user_id' => $this->etudiant()->id,
            'livre_id' => Livre::factory()->create()->id,
            'date_emprunt' => now()->subDays(60)->toDateString(),
            'date_retour_prevue' => now()->subDays(50)->toDateString(),
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);

        $this->assertSame(50, $emprunt->joursRetard());
        $this->assertSame(300.0, $emprunt->calculerPenaliteRetard());
    }

    public function test_les_jours_de_grace_reportent_la_penalite(): void
    {
        Parametres::definir('penalite.jours_grace', 3);

        $emprunt = Emprunt::create([
            'user_id' => $this->etudiant()->id,
            'livre_id' => Livre::factory()->create()->id,
            'date_emprunt' => now()->subDays(20)->toDateString(),
            'date_retour_prevue' => now()->subDays(2)->toDateString(),
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);

        $this->assertSame(2, $emprunt->joursRetard());
        $this->assertSame(0.0, $emprunt->calculerPenaliteRetard());
    }

    public function test_un_retour_en_mauvais_etat_genere_une_penalite_de_degradation(): void
    {
        $livre = $this->livreAvecExemplaires(1);
        $emprunt = $this->service->enregistrerEmprunt($this->etudiant(), $livre);

        $this->service->enregistrerRetour($emprunt, 'mauvais');

        $penalite = Penalite::where('type', Penalite::TYPE_DEGRADATION)->first();
        $this->assertNotNull($penalite);
        $this->assertEquals(Parametres::decimal('penalite.montant_degradation'), (float) $penalite->montant);
        $this->assertSame(Exemplaire::STATUT_ENDOMMAGE, $emprunt->exemplaire->fresh()->statut);
    }

    public function test_un_ouvrage_declare_perdu_genere_une_penalite_forfaitaire(): void
    {
        $livre = $this->livreAvecExemplaires(1);
        $emprunt = $this->service->enregistrerEmprunt($this->etudiant(), $livre);

        $this->service->enregistrerRetour($emprunt, 'perdu');

        $penalite = Penalite::where('type', Penalite::TYPE_PERTE)->first();
        $this->assertNotNull($penalite);
        $this->assertSame(Exemplaire::STATUT_PERDU, $emprunt->exemplaire->fresh()->statut);
        $this->assertSame(Emprunt::STATUT_PERDU, $emprunt->fresh()->statut);
    }

    public function test_un_emprunt_deja_rendu_ne_peut_pas_l_etre_deux_fois(): void
    {
        $emprunt = $this->service->enregistrerEmprunt($this->etudiant(), $this->livreAvecExemplaires(1));
        $this->service->enregistrerRetour($emprunt, 'bon');

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('déjà été retourné');

        $this->service->enregistrerRetour($emprunt->fresh(), 'bon');
    }

    public function test_la_commande_de_traitement_des_retards_marque_et_penalise(): void
    {
        $etudiant = $this->etudiant();
        $livre = $this->livreAvecExemplaires(1);

        Emprunt::create([
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'exemplaire_id' => $livre->exemplaires()->first()->id,
            'date_emprunt' => now()->subDays(20)->toDateString(),
            'date_retour_prevue' => now()->subDays(4)->toDateString(),
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);

        $this->artisan('bibliotheque:traiter-retards --sans-notification')->assertSuccessful();

        $this->assertSame(1, Emprunt::where('statut', Emprunt::STATUT_EN_RETARD)->count());
        $this->assertSame(1, Penalite::where('type', Penalite::TYPE_RETARD)->count());
    }

    public function test_le_guichet_retrouve_l_emprunt_par_code_barre(): void
    {
        $livre = $this->livreAvecExemplaires(1);
        $exemplaire = $livre->exemplaires()->first();
        $exemplaire->update(['code_barre' => 'GUI-0001']);

        $this->service->enregistrerEmprunt($this->etudiant(), $livre, $exemplaire->fresh());

        $this->actingAs($this->bibliothecaire())
            ->get(route('emprunts.guichet', ['code_barre' => 'GUI-0001']))
            ->assertOk()
            ->assertSee('Emprunt trouvé');
    }

    public function test_le_bibliothecaire_enregistre_un_emprunt_depuis_le_formulaire(): void
    {
        $etudiant = $this->etudiant();
        $livre = $this->livreAvecExemplaires(2);

        $this->actingAs($this->bibliothecaire())
            ->post(route('emprunts.store'), [
                'user_id' => $etudiant->id,
                'livre_id' => $livre->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('emprunts', [
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);
    }

    public function test_un_etudiant_ne_peut_pas_enregistrer_un_emprunt(): void
    {
        $this->actingAs($this->etudiant())
            ->post(route('emprunts.store'), [
                'user_id' => $this->etudiant()->id,
                'livre_id' => Livre::factory()->create()->id,
            ])
            ->assertForbidden();
    }
}
