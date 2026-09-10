<?php

namespace Tests\Feature;

use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExemplaireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    public function test_un_bibliothecaire_ajoute_plusieurs_exemplaires_d_un_coup(): void
    {
        $livre = Livre::factory()->create(['exemplaires_totaux' => 0, 'exemplaires_disponibles' => 0]);

        $this->actingAs($this->bibliothecaire())
            ->post(route('exemplaires.store'), [
                'livre_id' => $livre->id,
                'etat' => 'bon',
                'statut' => 'disponible',
                'quantite' => 4,
            ])
            ->assertRedirect(route('livres.show', $livre));

        $this->assertSame(4, $livre->exemplaires()->count());
        $this->assertSame(4, $livre->fresh()->exemplaires_disponibles);
        $this->assertCount(4, $livre->exemplaires->pluck('code_barre')->unique());
    }

    public function test_le_code_barre_doit_rester_unique(): void
    {
        $livre = Livre::factory()->create();
        Exemplaire::factory()->create(['livre_id' => $livre->id, 'code_barre' => 'ALG-0001']);

        $this->actingAs($this->bibliothecaire())
            ->post(route('exemplaires.store'), [
                'livre_id' => $livre->id,
                'code_barre' => 'ALG-0001',
                'etat' => 'bon',
                'statut' => 'disponible',
            ])
            ->assertSessionHasErrors('code_barre');
    }

    public function test_un_exemplaire_emprunte_ne_peut_pas_etre_supprime(): void
    {
        $livre = Livre::factory()->create();
        $exemplaire = Exemplaire::factory()->emprunte()->create(['livre_id' => $livre->id]);

        Emprunt::create([
            'user_id' => $this->etudiant()->id,
            'livre_id' => $livre->id,
            'exemplaire_id' => $exemplaire->id,
            'date_emprunt' => now()->toDateString(),
            'date_retour_prevue' => now()->addDays(14)->toDateString(),
            'statut' => 'en cours',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('exemplaires.destroy', $exemplaire))
            ->assertSessionHas('error');

        $this->assertModelExists($exemplaire);
    }

    public function test_la_recherche_par_code_barre_retourne_l_exemplaire(): void
    {
        $livre = Livre::factory()->create(['titre' => 'Réseaux informatiques']);
        Exemplaire::factory()->create(['livre_id' => $livre->id, 'code_barre' => 'RES-0007']);

        $this->actingAs($this->bibliothecaire())
            ->getJson(route('exemplaires.recherche', ['code_barre' => 'RES-0007']))
            ->assertOk()
            ->assertJsonPath('trouve', true)
            ->assertJsonPath('exemplaire.titre', 'Réseaux informatiques')
            ->assertJsonPath('exemplaire.disponible', true);
    }

    public function test_un_code_barre_inconnu_retourne_un_message_clair(): void
    {
        $this->actingAs($this->bibliothecaire())
            ->getJson(route('exemplaires.recherche', ['code_barre' => 'INCONNU']))
            ->assertNotFound()
            ->assertJsonPath('trouve', false);
    }

    public function test_les_compteurs_du_livre_suivent_l_etat_des_exemplaires(): void
    {
        $livre = Livre::factory()->create();
        Exemplaire::factory()->count(3)->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        $this->assertSame(3, $livre->exemplaires_disponibles);

        $livre->exemplaires()->first()->update(['statut' => Exemplaire::STATUT_PERDU]);
        $livre->synchroniserCompteurs();

        $this->assertSame(2, $livre->fresh()->exemplaires_disponibles);
        $this->assertSame(3, $livre->fresh()->exemplaires_totaux);
    }

    public function test_l_etiquette_contient_le_code_barre_et_le_qr_code(): void
    {
        $exemplaire = Exemplaire::factory()->create(['code_barre' => 'ETI-0001']);

        $this->actingAs($this->bibliothecaire())
            ->get(route('exemplaires.etiquette', $exemplaire))
            ->assertOk()
            ->assertSee('ETI-0001')
            ->assertSee('data:image/svg+xml;base64,', false);
    }
}
