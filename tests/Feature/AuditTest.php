<?php

namespace Tests\Feature;

use App\Models\Exemplaire;
use App\Models\JournalActivite;
use App\Models\Livre;
use App\Services\EmpruntService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        Notification::fake();
    }

    public function test_la_creation_d_un_ouvrage_est_tracee(): void
    {
        $bibliothecaire = $this->bibliothecaire();

        $this->actingAs($bibliothecaire)->post(route('livres.store'), [
            'isbn' => '978-2-1111-1111-1',
            'titre' => 'Ouvrage tracé',
            'auteur' => 'Auteur test',
            'editeur' => 'Éditeur test',
            'annee_publication' => 2024,
            'categorie' => 'Informatique',
            'type_document' => 'livre',
            'langue' => 'Français',
            'nombre_pages' => 100,
            'emplacement_rayon' => 'INF-01',
            'exemplaires_totaux' => 1,
        ]);

        $journal = JournalActivite::where('action', 'creation')->where('module', 'catalogue')->first();

        $this->assertNotNull($journal);
        $this->assertSame($bibliothecaire->id, $journal->user_id);
        $this->assertStringContainsString('Ouvrage tracé', $journal->description);
        $this->assertNotNull($journal->adresse_ip);
    }

    public function test_l_emprunt_et_le_retour_sont_traces(): void
    {
        $bibliothecaire = $this->bibliothecaire();
        $this->actingAs($bibliothecaire);

        $livre = Livre::factory()->create(['exemplaires_totaux' => 1]);
        Exemplaire::factory()->create(['livre_id' => $livre->id]);
        $livre->synchroniserCompteurs();

        $service = app(EmpruntService::class);
        $emprunt = $service->enregistrerEmprunt($this->etudiant(), $livre->fresh());
        $service->enregistrerRetour($emprunt, 'bon');

        $this->assertSame(1, JournalActivite::where('action', 'emprunt')->count());
        $this->assertSame(1, JournalActivite::where('action', 'retour')->count());

        $trace = JournalActivite::where('action', 'emprunt')->first();
        $this->assertSame($bibliothecaire->id, $trace->user_id);
        $this->assertSame(\App\Models\Emprunt::class, $trace->sujet_type);
    }

    public function test_le_changement_de_role_est_trace(): void
    {
        $admin = $this->admin();
        $cible = $this->etudiant();

        $this->actingAs($admin)->put(route('users.roles', $cible), [
            'role' => 'bibliothecaire',
        ])->assertSessionHas('success');

        $this->assertSame('bibliothecaire', $cible->fresh()->role);
        $this->assertSame(1, JournalActivite::where('action', 'permission')->count());
    }

    public function test_seul_un_habilite_consulte_le_journal(): void
    {
        JournalActivite::create([
            'action' => 'creation', 'module' => 'test', 'description' => 'Entrée test',
        ]);

        $this->actingAs($this->admin())->get(route('audit.index'))->assertOk()->assertSee('Entrée test');
        $this->actingAs($this->bibliothecaire())->get(route('audit.index'))->assertForbidden();
        $this->actingAs($this->etudiant())->get(route('audit.index'))->assertForbidden();
    }

    public function test_le_journal_ne_contient_jamais_de_mot_de_passe(): void
    {
        $this->actingAs($this->admin())->post(route('users.store'), [
            'name' => 'Nouveau compte',
            'email' => 'nouveau@univ.test',
            'matricule' => 'ETU-9999',
            'password' => 'motdepasse-secret',
            'password_confirmation' => 'motdepasse-secret',
            'role' => 'etudiant',
            'statut' => 'actif',
        ])->assertRedirect();

        $journal = JournalActivite::where('module', 'usagers')->first();

        $this->assertNotNull($journal);
        $this->assertArrayNotHasKey('password', $journal->donnees ?? []);
        $this->assertStringNotContainsString('motdepasse-secret', json_encode($journal->donnees));
    }
}
