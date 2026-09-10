<?php

namespace Tests\Feature;

use App\Exceptions\RegleMetierException;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Services\EmpruntService;
use App\Services\ReservationService;
use App\Support\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RenouvellementTest extends TestCase
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

    private function empruntEnCours(?\App\Models\User $usager = null): Emprunt
    {
        $livre = Livre::factory()->create(['exemplaires_totaux' => 2]);
        Exemplaire::factory()->count(2)->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        return $this->service->enregistrerEmprunt($usager ?? $this->etudiant(), $livre->fresh());
    }

    public function test_un_emprunt_sans_obstacle_est_renouvele(): void
    {
        $emprunt = $this->empruntEnCours();
        $ancienneEcheance = $emprunt->date_retour_prevue->copy();

        $renouvellement = $this->service->renouveler($emprunt, $emprunt->user);

        $this->assertSame('accepte', $renouvellement->statut);
        $this->assertSame(1, $emprunt->fresh()->nombre_renouvellements);
        $this->assertSame(
            $ancienneEcheance->addDays($emprunt->user->dureeEmprunt())->toDateString(),
            $emprunt->fresh()->date_retour_prevue->toDateString()
        );
    }

    public function test_le_nombre_maximal_de_renouvellements_est_respecte(): void
    {
        $emprunt = $this->empruntEnCours();
        $maximum = $emprunt->user->maxRenouvellements();

        for ($i = 0; $i < $maximum; $i++) {
            $this->service->renouveler($emprunt->fresh(), $emprunt->user);
        }

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('nombre maximal de renouvellements');

        $this->service->renouveler($emprunt->fresh(), $emprunt->user);
    }

    public function test_un_emprunt_en_retard_ne_peut_pas_etre_renouvele(): void
    {
        $emprunt = $this->empruntEnCours();
        $emprunt->update(['date_retour_prevue' => now()->subDays(3)->toDateString()]);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('en retard');

        $this->service->renouveler($emprunt->fresh(), $emprunt->user);
    }

    public function test_un_ouvrage_reserve_bloque_le_renouvellement(): void
    {
        $emprunt = $this->empruntEnCours();

        app(ReservationService::class)->reserver($this->etudiant(), $emprunt->livre);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('réservé par un autre usager');

        $this->service->renouveler($emprunt->fresh(), $emprunt->user);
    }

    public function test_la_validation_par_un_bibliothecaire_peut_etre_exigee(): void
    {
        Parametres::definir('renouvellement.validation_requise', true);

        $emprunt = $this->empruntEnCours();
        $echeanceInitiale = $emprunt->date_retour_prevue->toDateString();

        $renouvellement = $this->service->renouveler($emprunt, $emprunt->user);

        $this->assertSame('en_attente', $renouvellement->statut);
        $this->assertSame($echeanceInitiale, $emprunt->fresh()->date_retour_prevue->toDateString());
        $this->assertSame(0, $emprunt->fresh()->nombre_renouvellements);
    }

    public function test_un_bibliothecaire_accepte_une_demande_en_attente(): void
    {
        Parametres::definir('renouvellement.validation_requise', true);

        $emprunt = $this->empruntEnCours();
        $renouvellement = $this->service->renouveler($emprunt, $emprunt->user);

        $this->actingAs($this->bibliothecaire())
            ->put(route('renouvellements.traiter', $renouvellement), ['decision' => 'accepter'])
            ->assertSessionHas('success');

        $this->assertSame('accepte', $renouvellement->fresh()->statut);
        $this->assertSame(1, $emprunt->fresh()->nombre_renouvellements);
    }

    public function test_un_bibliothecaire_refuse_une_demande_avec_motif(): void
    {
        Parametres::definir('renouvellement.validation_requise', true);

        $emprunt = $this->empruntEnCours();
        $renouvellement = $this->service->renouveler($emprunt, $emprunt->user);

        $this->actingAs($this->bibliothecaire())
            ->put(route('renouvellements.traiter', $renouvellement), [
                'decision' => 'refuser',
                'motif_refus' => 'Ouvrage demandé par un autre usager.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('refuse', $renouvellement->fresh()->statut);
        $this->assertSame(0, $emprunt->fresh()->nombre_renouvellements);
    }

    public function test_le_refus_exige_un_motif(): void
    {
        Parametres::definir('renouvellement.validation_requise', true);

        $renouvellement = $this->service->renouveler($this->empruntEnCours(), $this->etudiant());

        $this->actingAs($this->bibliothecaire())
            ->put(route('renouvellements.traiter', $renouvellement), ['decision' => 'refuser'])
            ->assertSessionHasErrors('motif_refus');
    }

    public function test_un_usager_ne_peut_pas_renouveler_l_emprunt_d_un_autre(): void
    {
        $emprunt = $this->empruntEnCours();

        $this->actingAs($this->etudiant())
            ->post(route('emprunts.renouveler', $emprunt))
            ->assertForbidden();
    }

    public function test_un_usager_renouvelle_son_propre_emprunt_depuis_son_espace(): void
    {
        $emprunt = $this->empruntEnCours();

        $this->actingAs($emprunt->user)
            ->post(route('emprunts.renouveler', $emprunt))
            ->assertSessionHas('success');

        $this->assertSame(1, $emprunt->fresh()->nombre_renouvellements);
        $this->assertDatabaseCount('renouvellements', 1);
    }
}
