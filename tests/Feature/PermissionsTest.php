<?php

namespace Tests\Feature;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    public function test_l_administrateur_dispose_de_toutes_les_permissions(): void
    {
        $admin = $this->admin();

        foreach (\App\Support\Permissions::toutes() as $permission) {
            $this->assertTrue($admin->peut($permission), "Permission manquante : {$permission}");
        }
    }

    public function test_le_bibliothecaire_ne_peut_pas_modifier_les_parametres(): void
    {
        $bibliothecaire = $this->bibliothecaire();

        $this->assertTrue($bibliothecaire->peut('emprunts.enregistrer'));
        $this->assertTrue($bibliothecaire->peut('livres.creer'));
        $this->assertFalse($bibliothecaire->peut('parametres.gerer'));
        $this->assertFalse($bibliothecaire->peut('usagers.roles'));
        $this->assertFalse($bibliothecaire->peut('livres.supprimer'));

        $this->actingAs($bibliothecaire)->get('/settings')->assertForbidden();
    }

    public function test_un_etudiant_ne_peut_ni_creer_ni_modifier_le_catalogue(): void
    {
        $etudiant = $this->etudiant();
        $livre = Livre::factory()->create();

        $this->actingAs($etudiant)->get(route('livres.create'))->assertForbidden();
        $this->actingAs($etudiant)->post(route('livres.store'), [])->assertForbidden();
        $this->actingAs($etudiant)->get(route('livres.edit', $livre))->assertForbidden();
        $this->actingAs($etudiant)->delete(route('livres.destroy', $livre))->assertForbidden();
    }

    public function test_un_etudiant_ne_peut_pas_consulter_la_liste_des_usagers(): void
    {
        $this->actingAs($this->etudiant())->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->etudiant())->get(route('etudiants.index'))->assertForbidden();
        $this->actingAs($this->etudiant())->get(route('audit.index'))->assertForbidden();
    }

    public function test_un_etudiant_ne_peut_pas_voir_l_emprunt_d_un_autre_etudiant(): void
    {
        $awa = $this->etudiant();
        $koffi = $this->etudiant();
        $livre = Livre::factory()->create();

        $empruntDeKoffi = Emprunt::create([
            'user_id' => $koffi->id,
            'livre_id' => $livre->id,
            'date_emprunt' => now()->toDateString(),
            'date_retour_prevue' => now()->addDays(14)->toDateString(),
            'statut' => Emprunt::STATUT_EN_COURS,
        ]);

        // L'identifiant dans l'URL ne donne aucun accès supplémentaire.
        $this->actingAs($awa)->get(route('emprunts.show', $empruntDeKoffi))->assertForbidden();
        $this->actingAs($koffi)->get(route('emprunts.show', $empruntDeKoffi))->assertOk();
    }

    public function test_un_etudiant_ne_voit_que_ses_propres_penalites(): void
    {
        $awa = $this->etudiant();
        $koffi = $this->etudiant();

        $penaliteKoffi = Penalite::create([
            'user_id' => $koffi->id, 'type' => 'retard', 'montant' => 500,
            'statut' => 'impayee', 'motif' => 'Motif confidentiel de Koffi',
        ]);

        $this->actingAs($awa)->get(route('penalites.show', $penaliteKoffi))->assertForbidden();

        $reponse = $this->actingAs($awa)->get(route('penalites.index'))->assertOk();
        $reponse->assertDontSee($penaliteKoffi->motif);
    }

    public function test_un_etudiant_ne_voit_que_ses_propres_reservations(): void
    {
        $awa = $this->etudiant();
        $koffi = $this->etudiant();
        $livre = Livre::factory()->create();

        $reservationKoffi = Reservation::create([
            'user_id' => $koffi->id, 'livre_id' => $livre->id,
            'date_reservation' => now()->toDateString(),
            'date_expiration' => now()->addDays(7)->toDateString(),
            'statut' => Reservation::STATUT_ACTIVE, 'position_file_attente' => 1,
        ]);

        $this->actingAs($awa)->get(route('reservations.show', $reservationKoffi))->assertForbidden();
    }

    public function test_un_usager_peut_consulter_sa_propre_fiche_mais_pas_celle_d_un_autre(): void
    {
        $awa = $this->etudiant();
        $koffi = $this->etudiant();

        $this->actingAs($awa)->get(route('users.show', $awa))->assertOk();
        $this->actingAs($awa)->get(route('users.show', $koffi))->assertForbidden();
    }

    public function test_un_bibliothecaire_ne_peut_pas_modifier_un_administrateur(): void
    {
        $bibliothecaire = $this->bibliothecaire();
        $admin = $this->admin();

        $this->actingAs($bibliothecaire)->get(route('users.edit', $admin))->assertForbidden();
    }

    public function test_les_permissions_d_un_role_sont_bien_appliquees(): void
    {
        $enseignant = $this->enseignant();

        $this->assertTrue($enseignant->peut('livres.voir'));
        $this->assertFalse($enseignant->peut('livres.creer'));
        $this->assertFalse($enseignant->peut('emprunts.enregistrer'));
    }
}
