<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthentificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    public function test_la_page_de_connexion_est_accessible(): void
    {
        $this->get('/login')->assertOk()->assertSee('Connexion', false);
    }

    public function test_un_utilisateur_peut_se_connecter_avec_ses_identifiants(): void
    {
        $user = $this->etudiant(['email' => 'awa@univ.test', 'password' => 'motdepasse']);

        $this->post('/login', [
            'email' => 'awa@univ.test',
            'password' => 'motdepasse',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_un_mot_de_passe_invalide_est_refuse(): void
    {
        $this->etudiant(['email' => 'awa@univ.test', 'password' => 'motdepasse']);

        $this->from('/login')->post('/login', [
            'email' => 'awa@univ.test',
            'password' => 'mauvais',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_un_compte_desactive_est_deconnecte_immediatement(): void
    {
        $user = $this->etudiant(['actif' => false, 'statut' => 'suspendu']);

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_les_pages_protegees_redirigent_vers_la_connexion(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/livres')->assertRedirect('/login');
        $this->get('/emprunts')->assertRedirect('/login');
    }

    public function test_l_inscription_publique_est_fermee(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_les_pages_publiques_sont_accessibles_sans_compte(): void
    {
        $this->get('/')->assertOk();
        $this->get('/faq')->assertOk();
        $this->get('/aide')->assertOk();
        $this->get('/guide')->assertOk();
        $this->get('/conditions-utilisation')->assertOk();
    }

    public function test_le_mot_de_passe_est_toujours_hache(): void
    {
        $user = User::factory()->create(['password' => 'motdepasse']);

        $this->assertNotSame('motdepasse', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('motdepasse', $user->password));
    }

    public function test_la_deconnexion_termine_la_session(): void
    {
        $this->actingAs($this->etudiant())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
