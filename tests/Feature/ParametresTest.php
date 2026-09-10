<?php

namespace Tests\Feature;

use App\Models\Parametre;
use App\Support\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    public function test_les_valeurs_par_defaut_sont_disponibles_sans_enregistrement(): void
    {
        Parametre::query()->delete();
        Parametres::viderCache();

        $this->assertSame(14, Parametres::entier('emprunt.duree_etudiant'));
        $this->assertSame(100.0, Parametres::decimal('penalite.montant_par_jour'));
        $this->assertTrue(Parametres::booleen('notification.interne_active'));
    }

    public function test_une_valeur_enregistree_remplace_le_defaut(): void
    {
        Parametres::definir('emprunt.duree_etudiant', 21);

        $this->assertSame(21, Parametres::entier('emprunt.duree_etudiant'));
        $this->assertDatabaseHas('parametres', ['cle' => 'emprunt.duree_etudiant', 'valeur' => '21']);
    }

    public function test_le_cache_est_vide_a_chaque_modification(): void
    {
        Parametres::definir('penalite.montant_par_jour', 250);
        $this->assertSame(250.0, Parametres::decimal('penalite.montant_par_jour'));

        Parametres::definir('penalite.montant_par_jour', 500);
        $this->assertSame(500.0, Parametres::decimal('penalite.montant_par_jour'));
    }

    public function test_les_booleens_sont_correctement_types(): void
    {
        Parametres::definir('notification.email_active', false);
        $this->assertFalse(Parametres::booleen('notification.email_active'));

        Parametres::definir('notification.email_active', true);
        $this->assertTrue(Parametres::booleen('notification.email_active'));
    }

    public function test_le_montant_est_formate_avec_la_devise(): void
    {
        Parametres::definir('general.devise', 'FCFA');

        $this->assertSame('12 500 FCFA', Parametres::formaterMontant(12500));
    }

    public function test_un_administrateur_modifie_les_parametres_depuis_l_interface(): void
    {
        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'groupe' => 'emprunt',
                'parametres' => [
                    'emprunt__duree_etudiant' => 21,
                    'emprunt__max_etudiant' => 5,
                ],
            ])
            ->assertSessionHas('success');

        $this->assertSame(21, Parametres::entier('emprunt.duree_etudiant'));
        $this->assertSame(5, Parametres::entier('emprunt.max_etudiant'));
    }

    public function test_un_bibliothecaire_ne_peut_pas_modifier_les_parametres(): void
    {
        $this->actingAs($this->bibliothecaire())
            ->put(route('settings.update'), ['groupe' => 'emprunt'])
            ->assertForbidden();
    }

    public function test_la_modification_du_quota_change_immediatement_la_regle_metier(): void
    {
        $etudiant = $this->etudiant();

        Parametres::definir('emprunt.max_etudiant', 7);
        $this->assertSame(7, $etudiant->quotaEmprunts());

        Parametres::definir('emprunt.max_etudiant', 2);
        $this->assertSame(2, $etudiant->quotaEmprunts());
    }
}
