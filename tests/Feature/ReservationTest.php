<?php

namespace Tests\Feature;

use App\Exceptions\RegleMetierException;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Reservation;
use App\Notifications\ReservationDisponible;
use App\Notifications\ReservationExpiree;
use App\Services\EmpruntService;
use App\Services\ReservationService;
use App\Support\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private ReservationService $reservations;

    private EmpruntService $emprunts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        $this->reservations = app(ReservationService::class);
        $this->emprunts = app(EmpruntService::class);
        Notification::fake();
    }

    private function livreEpuise(): Livre
    {
        $livre = Livre::factory()->create(['exemplaires_totaux' => 1]);
        Exemplaire::factory()->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        // Le seul exemplaire part en prêt : l'ouvrage devient indisponible.
        $this->emprunts->enregistrerEmprunt($this->etudiant(), $livre->fresh());

        return $livre->fresh();
    }

    public function test_la_file_d_attente_respecte_l_ordre_d_arrivee(): void
    {
        $livre = $this->livreEpuise();
        $etudiantA = $this->etudiant();
        $etudiantB = $this->etudiant();

        $reservationA = $this->reservations->reserver($etudiantA, $livre);
        $reservationB = $this->reservations->reserver($etudiantB, $livre);

        $this->assertSame(1, $reservationA->position_file_attente);
        $this->assertSame(2, $reservationB->position_file_attente);
    }

    public function test_reserver_ne_consomme_pas_de_stock(): void
    {
        $livre = Livre::factory()->create(['exemplaires_totaux' => 2]);
        Exemplaire::factory()->count(2)->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        $disponiblesAvant = $livre->fresh()->exemplaires_disponibles;
        $this->reservations->reserver($this->etudiant(), $livre->fresh());

        // Un exemplaire est mis de côté pour le réservataire, pas supprimé du stock.
        $this->assertSame(2, $livre->fresh()->exemplaires()->count());
        $this->assertSame($disponiblesAvant - 1, $livre->fresh()->exemplaires_disponibles);
        $this->assertSame(1, $livre->fresh()->exemplaires()->where('statut', Exemplaire::STATUT_RESERVE)->count());
    }

    public function test_le_premier_de_la_file_est_notifie_au_retour_de_l_exemplaire(): void
    {
        $livre = $this->livreEpuise();
        $etudiantA = $this->etudiant();
        $etudiantB = $this->etudiant();

        $this->reservations->reserver($etudiantA, $livre);
        $this->reservations->reserver($etudiantB, $livre);

        $emprunt = $livre->emprunts()->enCours()->first();
        $this->emprunts->enregistrerRetour($emprunt, 'bon');

        $reservationA = Reservation::where('user_id', $etudiantA->id)->first();
        $reservationB = Reservation::where('user_id', $etudiantB->id)->first();

        $this->assertNotNull($reservationA->date_notification, 'Le premier de la file doit être notifié.');
        $this->assertNull($reservationB->date_notification, 'Le suivant attend son tour.');

        Notification::assertSentTo($etudiantA, ReservationDisponible::class);
        Notification::assertNotSentTo($etudiantB, ReservationDisponible::class);

        // L'exemplaire est mis de côté, pas remis en rayon.
        $this->assertSame(Exemplaire::STATUT_RESERVE, $emprunt->exemplaire->fresh()->statut);
    }

    public function test_une_reservation_non_retiree_expire_et_passe_au_suivant(): void
    {
        $livre = $this->livreEpuise();
        $etudiantA = $this->etudiant();
        $etudiantB = $this->etudiant();

        $this->reservations->reserver($etudiantA, $livre);
        $this->reservations->reserver($etudiantB, $livre);

        $this->emprunts->enregistrerRetour($livre->emprunts()->enCours()->first(), 'bon');

        // Le délai de retrait est dépassé.
        Reservation::where('user_id', $etudiantA->id)
            ->update(['date_limite_retrait' => now()->subDay()->toDateString()]);

        $expirees = $this->reservations->expirerReservationsDepassees();

        $this->assertSame(1, $expirees);
        $this->assertSame(Reservation::STATUT_EXPIREE, Reservation::where('user_id', $etudiantA->id)->first()->statut);

        $reservationB = Reservation::where('user_id', $etudiantB->id)->first();
        $this->assertSame(1, $reservationB->position_file_attente, 'B remonte en tête de file.');
        $this->assertNotNull($reservationB->date_notification, 'B est notifié à son tour.');

        Notification::assertSentTo($etudiantA, ReservationExpiree::class);
        Notification::assertSentTo($etudiantB, ReservationDisponible::class);
    }

    public function test_la_reservation_est_honoree_lorsque_l_usager_retire_l_ouvrage(): void
    {
        $livre = $this->livreEpuise();
        $etudiant = $this->etudiant();

        $this->reservations->reserver($etudiant, $livre);
        $this->emprunts->enregistrerRetour($livre->emprunts()->enCours()->first(), 'bon');

        $this->emprunts->enregistrerEmprunt($etudiant->fresh(), $livre->fresh());

        $this->assertSame(
            Reservation::STATUT_HONOREE,
            Reservation::where('user_id', $etudiant->id)->first()->statut
        );
    }

    public function test_un_usager_ne_peut_pas_reserver_deux_fois_le_meme_ouvrage(): void
    {
        $livre = $this->livreEpuise();
        $etudiant = $this->etudiant();

        $this->reservations->reserver($etudiant, $livre);

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('déjà une réservation active');

        $this->reservations->reserver($etudiant->fresh(), $livre);
    }

    public function test_le_nombre_de_reservations_actives_est_plafonne(): void
    {
        $etudiant = $this->etudiant();
        $maximum = Parametres::entier('reservation.max_par_usager');

        for ($i = 0; $i < $maximum; $i++) {
            $this->reservations->reserver($etudiant->fresh(), $this->livreEpuise());
        }

        $this->expectException(RegleMetierException::class);
        $this->expectExceptionMessage('nombre maximal de réservations');

        $this->reservations->reserver($etudiant->fresh(), $this->livreEpuise());
    }

    public function test_l_annulation_libere_l_exemplaire_et_renumerote_la_file(): void
    {
        $livre = $this->livreEpuise();
        $etudiantA = $this->etudiant();
        $etudiantB = $this->etudiant();

        $reservationA = $this->reservations->reserver($etudiantA, $livre);
        $this->reservations->reserver($etudiantB, $livre);

        $this->reservations->annuler($reservationA, 'Plus besoin');

        $this->assertSame(Reservation::STATUT_ANNULEE, $reservationA->fresh()->statut);
        $this->assertSame(1, Reservation::where('user_id', $etudiantB->id)->first()->position_file_attente);
    }

    public function test_un_etudiant_reserve_depuis_la_fiche_de_l_ouvrage(): void
    {
        $livre = $this->livreEpuise();
        $etudiant = $this->etudiant();

        $this->actingAs($etudiant)
            ->post(route('reserver', $livre))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'statut' => Reservation::STATUT_ACTIVE,
        ]);
    }

    public function test_la_commande_d_expiration_traite_les_reservations_depassees(): void
    {
        $livre = $this->livreEpuise();
        $etudiant = $this->etudiant();

        $reservation = $this->reservations->reserver($etudiant, $livre);
        $reservation->update(['date_expiration' => now()->subDays(2)->toDateString()]);

        $this->artisan('bibliotheque:expirer-reservations')->assertSuccessful();

        $this->assertSame(Reservation::STATUT_EXPIREE, $reservation->fresh()->statut);
    }
}
