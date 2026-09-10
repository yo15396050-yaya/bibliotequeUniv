<?php

namespace Tests\Feature;

use App\Exceptions\RegleMetierException;
use App\Models\Penalite;
use App\Services\PenaliteService;
use App\Support\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PenaliteTest extends TestCase
{
    use RefreshDatabase;

    private PenaliteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        $this->service = app(PenaliteService::class);
        Notification::fake();
    }

    public function test_une_penalite_forfaitaire_utilise_le_montant_configure(): void
    {
        $etudiant = $this->etudiant();

        $penalite = $this->service->creerPenalite($etudiant, Penalite::TYPE_PERTE);

        $this->assertEquals(Parametres::decimal('penalite.montant_perte'), (float) $penalite->montant);
        $this->assertSame(Penalite::STATUT_IMPAYEE, $penalite->statut);
    }

    public function test_un_paiement_partiel_met_la_penalite_en_statut_intermediaire(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 1000, 'Test');

        $this->service->enregistrerPaiement($penalite, 400, 'especes');
        $penalite->refresh();

        $this->assertSame(Penalite::STATUT_PARTIELLE, $penalite->statut);
        $this->assertEquals(400.0, (float) $penalite->montant_paye);
        $this->assertEquals(600.0, $penalite->reste_a_payer);
    }

    public function test_un_paiement_integral_solde_la_penalite(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 1000, 'Test');

        $this->service->enregistrerPaiement($penalite, 600, 'especes');
        $this->service->enregistrerPaiement($penalite->fresh(), 400, 'mobile_money');

        $penalite->refresh();

        $this->assertSame(Penalite::STATUT_PAYEE, $penalite->statut);
        $this->assertEquals(0.0, $penalite->reste_a_payer);
        $this->assertSame(2, $penalite->paiements()->count());
    }

    public function test_un_paiement_superieur_au_reste_du_est_refuse(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 1000, 'Test');

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('dépasse le reste à payer');

        $this->service->enregistrerPaiement($penalite, 1500);
    }

    public function test_une_penalite_payee_ne_peut_pas_etre_annulee(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 500, 'Test');
        $this->service->enregistrerPaiement($penalite, 500);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('déjà payée');

        $this->service->annuler($penalite->fresh(), 'Erreur de saisie');
    }

    public function test_une_penalite_annulee_ne_bloque_plus_l_usager(): void
    {
        $etudiant = $this->etudiant();
        $penalite = $this->service->creerPenalite($etudiant, Penalite::TYPE_PERTE, 50000, 'Test');

        $this->assertFalse($etudiant->fresh()->peutEmprunter());

        $this->service->annuler($penalite, 'Ouvrage finalement retrouvé');

        $this->assertTrue($etudiant->fresh()->peutEmprunter());
    }

    public function test_un_bibliothecaire_encaisse_un_paiement_depuis_l_interface(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 2000, 'Test');

        $this->actingAs($this->bibliothecaire())
            ->post(route('penalites.payer', $penalite), [
                'montant' => 2000,
                'mode_paiement' => 'especes',
            ])
            ->assertSessionHas('success');

        $this->assertSame(Penalite::STATUT_PAYEE, $penalite->fresh()->statut);
    }

    public function test_un_etudiant_ne_peut_pas_encaisser_de_paiement(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 2000, 'Test');

        $this->actingAs($this->etudiant())
            ->post(route('penalites.payer', $penalite), [
                'montant' => 2000,
                'mode_paiement' => 'especes',
            ])
            ->assertForbidden();
    }

    public function test_le_recu_pdf_est_genere(): void
    {
        $penalite = $this->service->creerPenalite($this->etudiant(), Penalite::TYPE_AUTRE, 1500, 'Test');

        $reponse = $this->actingAs($this->bibliothecaire())->get(route('penalites.recu', $penalite));

        $reponse->assertOk();
        $this->assertSame('application/pdf', $reponse->headers->get('content-type'));
    }
}
