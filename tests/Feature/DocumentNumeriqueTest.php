<?php

namespace Tests\Feature;

use App\Models\DocumentNumerique;
use App\Models\Livre;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Téléchargement sécurisé : aucun fichier n'est servi depuis un répertoire
 * public, chaque accès passe par une policy.
 */
class DocumentNumeriqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        Storage::fake(DocumentService::DISQUE);
    }

    private function ajouterDocument(Livre $livre, string $visibilite = 'authentifie', bool $telechargeable = true): DocumentNumerique
    {
        return app(DocumentService::class)->ajouter(
            $livre,
            UploadedFile::fake()->create('memoire.pdf', 120, 'application/pdf'),
            ['visibilite' => $visibilite, 'autoriser_telechargement' => $telechargeable]
        );
    }

    public function test_un_document_est_stocke_sur_le_disque_prive(): void
    {
        $this->actingAs($this->bibliothecaire());
        $livre = Livre::factory()->create();

        $document = $this->ajouterDocument($livre);

        Storage::disk(DocumentService::DISQUE)->assertExists($document->chemin);

        // Rien ne doit atterrir dans le répertoire public.
        $this->assertStringNotContainsString('public', $document->chemin);
        $this->assertTrue($livre->fresh()->estDisponibleNumerique());
    }

    public function test_l_envoi_passe_par_le_formulaire_et_refuse_les_formats_interdits(): void
    {
        $livre = Livre::factory()->create();

        $this->actingAs($this->bibliothecaire())
            ->post(route('documents.store', $livre), [
                'fichier' => UploadedFile::fake()->create('virus.exe', 10),
                'visibilite' => 'authentifie',
            ])
            ->assertSessionHasErrors('fichier');
    }

    public function test_un_etudiant_ne_peut_pas_ajouter_de_document(): void
    {
        $livre = Livre::factory()->create();

        $this->actingAs($this->etudiant())
            ->post(route('documents.store', $livre), [
                'fichier' => UploadedFile::fake()->create('memoire.pdf', 50, 'application/pdf'),
                'visibilite' => 'authentifie',
            ])
            ->assertForbidden();
    }

    public function test_un_document_accessible_est_bien_telecharge(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create());

        $this->actingAs($this->etudiant())
            ->get(route('documents.telecharger', $document))
            ->assertOk();

        $this->assertSame(1, $document->fresh()->nombre_telechargements);
    }

    public function test_un_document_reserve_au_personnel_est_refuse_a_un_etudiant(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create(), 'personnel');

        // Modifier l'identifiant dans l'URL ne donne aucun accès.
        $this->actingAs($this->etudiant())
            ->get(route('documents.telecharger', $document))
            ->assertForbidden();

        $this->actingAs($this->etudiant())
            ->get(route('documents.consulter', $document))
            ->assertForbidden();
    }

    public function test_un_document_reserve_aux_enseignants_est_refuse_a_un_etudiant(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create(), 'enseignant');

        $this->actingAs($this->etudiant())->get(route('documents.consulter', $document))->assertForbidden();
        $this->actingAs($this->enseignant())->get(route('documents.consulter', $document))->assertOk();
    }

    public function test_le_telechargement_est_bloque_quand_il_est_desactive(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create(), 'authentifie', false);

        $etudiant = $this->etudiant();

        // La consultation en ligne reste possible, le téléchargement non.
        $this->actingAs($etudiant)->get(route('documents.consulter', $document))->assertOk();
        $this->actingAs($etudiant)->get(route('documents.telecharger', $document))->assertForbidden();
    }

    public function test_un_visiteur_non_connecte_est_redirige(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create());

        auth()->logout();

        $this->get(route('documents.telecharger', $document))->assertRedirect(route('login'));
    }

    public function test_un_compte_suspendu_perd_l_acces_aux_documents(): void
    {
        $this->actingAs($this->bibliothecaire());
        $document = $this->ajouterDocument(Livre::factory()->create());

        $suspendu = $this->etudiant(['statut' => 'suspendu']);

        // Le middleware déconnecte le compte suspendu avant toute lecture.
        $this->actingAs($suspendu)
            ->get(route('documents.consulter', $document))
            ->assertRedirect(route('login'));
    }

    public function test_la_suppression_retire_le_fichier_du_disque(): void
    {
        $this->actingAs($this->bibliothecaire());
        $livre = Livre::factory()->create();
        $document = $this->ajouterDocument($livre);
        $chemin = $document->chemin;

        $this->actingAs($this->admin())
            ->delete(route('documents.destroy', $document))
            ->assertSessionHas('success');

        Storage::disk(DocumentService::DISQUE)->assertMissing($chemin);
        $this->assertFalse($livre->fresh()->estDisponibleNumerique());
    }
}
