<?php

namespace Tests\Feature;

use App\Http\Requests\StoreEtudiantRequest;
use App\Http\Requests\StoreExemplaireRequest;
use App\Http\Requests\StoreLivreRequest;
use App\Http\Requests\StorePenaliteRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateLivreRequest;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garde-fou : un champ déclaré obligatoire côté serveur doit exister dans le
 * formulaire correspondant, sinon l'écran est inutilisable même si toutes les
 * autres briques fonctionnent.
 */
class FormulairesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
        $this->seed(\Database\Seeders\ReferentielSeeder::class);
    }

    /**
     * @return array<int, string> champs marqués « required » dans la requête
     */
    private function champsObligatoires(FormRequest $requete): array
    {
        return collect($requete->rules())
            ->filter(fn ($regles) => in_array('required', is_array($regles) ? $regles : explode('|', $regles), true))
            ->keys()
            ->reject(fn (string $champ) => str_contains($champ, '.')) // ignore les règles de tableau
            ->values()
            ->all();
    }

    private function assertFormulaireContient(string $html, array $champs, string $contexte): void
    {
        $manquants = array_values(array_filter(
            $champs,
            fn (string $champ) => ! str_contains($html, 'name="'.$champ.'"')
                && ! str_contains($html, 'name="'.$champ.'[]"')
        ));

        $this->assertSame([], $manquants, sprintf(
            '%s : champ(s) obligatoire(s) absent(s) du formulaire : %s',
            $contexte,
            implode(', ', $manquants)
        ));
    }

    public function test_le_formulaire_de_creation_d_ouvrage_couvre_tous_les_champs_requis(): void
    {
        $html = $this->actingAs($this->bibliothecaire())
            ->get(route('livres.create'))->assertOk()->getContent();

        $this->assertFormulaireContient(
            $html,
            $this->champsObligatoires(new StoreLivreRequest),
            'Création d\'ouvrage'
        );
    }

    public function test_le_formulaire_de_modification_d_ouvrage_couvre_tous_les_champs_requis(): void
    {
        $livre = Livre::factory()->create();

        $html = $this->actingAs($this->bibliothecaire())
            ->get(route('livres.edit', $livre))->assertOk()->getContent();

        // La modification impose en plus le statut de l'ouvrage.
        $this->assertFormulaireContient(
            $html,
            $this->champsObligatoires(new UpdateLivreRequest),
            'Modification d\'ouvrage'
        );
    }

    public function test_le_formulaire_d_exemplaire_couvre_tous_les_champs_requis(): void
    {
        $html = $this->actingAs($this->bibliothecaire())
            ->get(route('exemplaires.create'))->assertOk()->getContent();

        $this->assertFormulaireContient(
            $html,
            $this->champsObligatoires(new StoreExemplaireRequest),
            'Création d\'exemplaire'
        );
    }

    public function test_les_formulaires_d_usagers_couvrent_tous_les_champs_requis(): void
    {
        $champs = $this->champsObligatoires(new StoreUserRequest);

        $htmlCompte = $this->actingAs($this->admin())
            ->get(route('users.create'))->assertOk()->getContent();
        $this->assertFormulaireContient($htmlCompte, $champs, 'Création de compte');

        // L'écran « étudiant » fixe le rôle : sa requête dédiée ne l'exige pas.
        $htmlEtudiant = $this->actingAs($this->admin())
            ->get(route('etudiants.create'))->assertOk()->getContent();
        $this->assertFormulaireContient(
            $htmlEtudiant,
            $this->champsObligatoires(new StoreEtudiantRequest),
            'Création d\'étudiant'
        );
        $this->assertNotContains('role', $this->champsObligatoires(new StoreEtudiantRequest));
    }

    public function test_le_formulaire_de_penalite_couvre_tous_les_champs_requis(): void
    {
        $html = $this->actingAs($this->bibliothecaire())
            ->get(route('penalites.create'))->assertOk()->getContent();

        $this->assertFormulaireContient(
            $html,
            $this->champsObligatoires(new StorePenaliteRequest),
            'Création de pénalité'
        );
    }

    public function test_la_fiche_d_un_ouvrage_expose_ses_exemplaires_et_ses_documents(): void
    {
        $livre = Livre::factory()->create();
        Exemplaire::factory()->create(['livre_id' => $livre->id, 'code_barre' => 'FIC-0001']);

        $reponse = $this->actingAs($this->bibliothecaire())
            ->get(route('livres.show', $livre))->assertOk();

        $reponse->assertSee('FIC-0001');
        $reponse->assertSee('Documents numériques');
        // Le personnel doit pouvoir ajouter un exemplaire depuis la fiche.
        $reponse->assertSee(route('exemplaires.create', ['livre_id' => $livre->id]), false);
    }

    public function test_l_ajout_d_un_ouvrage_fonctionne_avec_les_donnees_du_formulaire(): void
    {
        // Reproduit exactement ce qu'un navigateur enverrait depuis l'écran.
        $donnees = [
            'isbn' => '978-2-7654-3210-9',
            'titre' => 'Systèmes distribués',
            'sous_titre' => '',
            'type_document' => 'livre',
            'annee_publication' => 2024,
            'edition' => '2e édition',
            'auteur' => 'Traoré Salif',
            'editeur' => 'Presses Universitaires',
            'editeur_id' => '',
            'categorie' => 'Informatique',
            'categorie_id' => '',
            'niveau_academique' => 'master',
            'domaine' => 'Informatique',
            'langue' => 'Français',
            'mots_cles' => 'réseaux, systèmes',
            'nombre_pages' => 350,
            'resume' => 'Un ouvrage de référence.',
            'description' => '',
            'emplacement_id' => '',
            'emplacement_rayon' => 'INF-09',
            'exemplaires_totaux' => 2,
            'generer_exemplaires' => '1',
            'visibilite_document' => 'authentifie',
            'autoriser_telechargement' => '1',
        ];

        $this->actingAs($this->bibliothecaire())
            ->post(route('livres.store'), $donnees)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $livre = Livre::firstWhere('isbn', '978-2-7654-3210-9');

        $this->assertNotNull($livre);
        $this->assertSame(2, $livre->exemplaires()->count());
        $this->assertSame('master', $livre->niveau_academique);
    }

    public function test_la_modification_d_un_ouvrage_fonctionne_avec_les_donnees_du_formulaire(): void
    {
        $livre = Livre::factory()->create(['titre' => 'Ancien titre']);

        $this->actingAs($this->bibliothecaire())
            ->put(route('livres.update', $livre), [
                'isbn' => $livre->isbn,
                'titre' => 'Nouveau titre',
                'type_document' => 'these',
                'annee_publication' => 2022,
                'auteur' => $livre->auteur,
                'editeur' => $livre->editeur,
                'categorie' => $livre->categorie,
                'langue' => 'Français',
                'nombre_pages' => 200,
                'emplacement_rayon' => 'INF-01',
                'exemplaires_totaux' => 3,
                'statut' => 'disponible',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Nouveau titre', $livre->fresh()->titre);
        $this->assertSame('these', $livre->fresh()->type_document);
    }

    public function test_la_creation_d_un_compte_fonctionne_avec_les_donnees_du_formulaire(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'Bibliothécaire Adjoint',
                'prenom' => 'Sara',
                'matricule' => 'BIB-2025-01',
                'email' => 'sara@univ.test',
                'role' => 'bibliothecaire',
                'statut' => 'actif',
                'telephone' => '0700000088',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'matricule' => 'BIB-2025-01',
            'role' => 'bibliothecaire',
        ]);
    }

    public function test_le_personnel_reserve_pour_un_usager_depuis_le_formulaire(): void
    {
        $etudiant = $this->etudiant();
        $livre = Livre::factory()->create();

        $this->actingAs($this->bibliothecaire())
            ->post(route('reservations.store'), [
                'user_id' => $etudiant->id,
                'livre_id' => $livre->id,
                'notes' => 'Demande au guichet',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'statut' => 'active',
        ]);
    }

    public function test_l_enregistrement_d_un_etudiant_fonctionne_avec_les_donnees_du_formulaire(): void
    {
        $this->actingAs($this->bibliothecaire())
            ->post(route('etudiants.store'), [
                'name' => 'Kouassi Awa',
                'prenom' => 'Awa',
                'matricule' => 'ETU-2025-777',
                'email' => 'awa.kouassi@univ.test',
                'telephone' => '0700000077',
                'faculte' => 'Faculté des Sciences',
                'departement' => 'Informatique',
                'filiere' => 'Informatique',
                'niveau' => 'Licence 3',
                'statut' => 'actif',
                'adresse' => 'Campus',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $etudiant = User::firstWhere('matricule', 'ETU-2025-777');

        $this->assertNotNull($etudiant);
        $this->assertSame(User::ROLE_ETUDIANT, $etudiant->role);
        // Mot de passe provisoire = matricule.
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('ETU-2025-777', $etudiant->password));
    }
}
