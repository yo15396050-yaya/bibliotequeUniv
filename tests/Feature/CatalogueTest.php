<?php

namespace Tests\Feature;

use App\Models\Auteur;
use App\Models\Categorie;
use App\Models\Editeur;
use App\Models\Exemplaire;
use App\Models\Livre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    private function donneesLivre(array $remplacements = []): array
    {
        return array_merge([
            'isbn' => '978-2-1234-5680-3',
            'titre' => 'Algorithmique avancée',
            'auteur' => 'Kouadio Konan',
            'editeur' => 'Presses Universitaires',
            'annee_publication' => 2023,
            'categorie' => 'Informatique',
            'type_document' => 'livre',
            'langue' => 'Français',
            'nombre_pages' => 420,
            'emplacement_rayon' => 'INF-04',
            'exemplaires_totaux' => 3,
            'generer_exemplaires' => 1,
        ], $remplacements);
    }

    public function test_un_bibliothecaire_cree_un_livre_avec_ses_exemplaires(): void
    {
        $bibliothecaire = $this->bibliothecaire();

        $this->actingAs($bibliothecaire)
            ->post(route('livres.store'), $this->donneesLivre())
            ->assertRedirect();

        $livre = Livre::firstWhere('isbn', '978-2-1234-5680-3');

        $this->assertNotNull($livre);
        $this->assertSame('Algorithmique avancée', $livre->titre);

        // Le livre et ses exemplaires sont deux choses distinctes.
        $this->assertSame(3, $livre->exemplaires()->count());
        $this->assertSame(3, $livre->exemplaires_disponibles);
        $this->assertSame(3, $livre->fresh()->exemplaires_totaux);

        // Chaque exemplaire possède un code-barres unique.
        $codes = $livre->exemplaires->pluck('code_barre');
        $this->assertCount(3, $codes->unique());
    }

    public function test_l_isbn_doit_etre_unique(): void
    {
        Livre::factory()->create(['isbn' => '978-2-1234-5680-3']);

        $this->actingAs($this->bibliothecaire())
            ->post(route('livres.store'), $this->donneesLivre())
            ->assertSessionHasErrors('isbn');
    }

    public function test_les_champs_obligatoires_sont_valides(): void
    {
        $this->actingAs($this->bibliothecaire())
            ->post(route('livres.store'), [])
            ->assertSessionHasErrors(['isbn', 'titre', 'auteur', 'editeur', 'annee_publication', 'categorie']);
    }

    public function test_un_livre_est_mis_a_jour_et_lie_a_ses_references(): void
    {
        $livre = Livre::factory()->create();
        $auteur = Auteur::factory()->create(['nom' => 'Diallo', 'prenom' => 'Fatou']);
        $editeur = Editeur::factory()->create(['nom' => 'Éditions Test']);
        $categorie = Categorie::create(['nom' => 'Réseaux', 'code_categorie' => 'RES']);

        $this->actingAs($this->bibliothecaire())
            ->put(route('livres.update', $livre), $this->donneesLivre([
                'isbn' => $livre->isbn,
                'titre' => 'Titre révisé',
                'statut' => 'disponible',
                'auteurs' => [$auteur->id],
                'editeur_id' => $editeur->id,
                'categorie_id' => $categorie->id,
            ]))
            ->assertRedirect();

        $livre->refresh();

        $this->assertSame('Titre révisé', $livre->titre);
        $this->assertSame($categorie->id, $livre->categorie_id);
        $this->assertSame('Réseaux', $livre->categorie);
        $this->assertSame('Éditions Test', $livre->editeur);
        $this->assertTrue($livre->auteurs->contains($auteur->id));
        // Le libellé texte suit les auteurs normalisés.
        $this->assertSame('Fatou Diallo', $livre->auteur);
    }

    public function test_un_livre_avec_un_emprunt_en_cours_ne_peut_pas_etre_supprime(): void
    {
        $livre = Livre::factory()->create();
        $exemplaire = Exemplaire::factory()->create(['livre_id' => $livre->id]);

        \App\Models\Emprunt::create([
            'user_id' => $this->etudiant()->id,
            'livre_id' => $livre->id,
            'exemplaire_id' => $exemplaire->id,
            'date_emprunt' => now()->toDateString(),
            'date_retour_prevue' => now()->addDays(14)->toDateString(),
            'statut' => 'en cours',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('livres.destroy', $livre))
            ->assertSessionHas('error');

        $this->assertModelExists($livre);
    }

    public function test_la_recherche_trouve_par_titre_auteur_isbn_et_code_barre(): void
    {
        $livre = Livre::factory()->create([
            'titre' => 'Cryptographie appliquée',
            'auteur' => 'Bamba Ismaël',
            'isbn' => '978-9-9999-0000-1',
            'mots_cles' => 'sécurité, chiffrement',
        ]);
        Exemplaire::factory()->create(['livre_id' => $livre->id, 'code_barre' => 'CRY-0001']);
        Livre::factory()->create(['titre' => 'Botanique générale']);

        foreach (['Cryptographie', 'Bamba', '978-9-9999-0000-1', 'chiffrement', 'CRY-0001'] as $terme) {
            $resultats = Livre::recherche($terme)->pluck('id');
            $this->assertTrue($resultats->contains($livre->id), "Recherche échouée pour « {$terme} »");
            $this->assertCount(1, $resultats, "Trop de résultats pour « {$terme} »");
        }
    }

    public function test_la_recherche_filtre_par_disponibilite_et_categorie(): void
    {
        Livre::factory()->create(['titre' => 'Livre A', 'categorie' => 'Droit', 'exemplaires_disponibles' => 0]);
        $disponible = Livre::factory()->create(['titre' => 'Livre B', 'categorie' => 'Droit', 'exemplaires_disponibles' => 2]);
        Livre::factory()->create(['titre' => 'Livre C', 'categorie' => 'Médecine', 'exemplaires_disponibles' => 1]);

        $resultats = Livre::filtres(['categorie' => 'Droit', 'disponible' => '1'])->get();

        $this->assertCount(1, $resultats);
        $this->assertSame($disponible->id, $resultats->first()->id);
    }

    public function test_la_page_de_recherche_unifiee_retourne_les_resultats(): void
    {
        Livre::factory()->create(['titre' => 'Intelligence artificielle appliquée']);

        $this->actingAs($this->etudiant())
            ->get(route('recherche', ['q' => 'Intelligence']))
            ->assertOk()
            ->assertSee('Intelligence artificielle appliquée');
    }

    public function test_la_suggestion_instantanee_repond_en_json(): void
    {
        Livre::factory()->create(['titre' => 'Bases de données réparties']);

        $this->actingAs($this->etudiant())
            ->getJson(route('recherche.suggestions', ['q' => 'Bases']))
            ->assertOk()
            ->assertJsonPath('resultats.0.titre', 'Bases de données réparties');
    }

    public function test_un_qr_code_est_genere_pour_un_ouvrage(): void
    {
        $livre = Livre::factory()->create();

        $this->actingAs($this->bibliothecaire())
            ->get(route('livres.qrcode', $livre))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false);
    }
}
