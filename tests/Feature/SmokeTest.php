<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Parcourt toutes les pages GET de l'application avec un compte administrateur
 * et vérifie qu'aucune ne renvoie d'erreur serveur (vue manquante, variable
 * indéfinie, route cassée...).
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerSocle();
    }

    public function test_toutes_les_pages_repondent_sans_erreur_serveur(): void
    {
        $this->seed(\Database\Seeders\ReferentielSeeder::class);

        $admin = $this->admin();
        $livre = \App\Models\Livre::factory()->create();
        $exemplaire = \App\Models\Exemplaire::factory()->create(['livre_id' => $livre->id]);
        $etudiant = $this->etudiant();
        $emprunt = \App\Models\Emprunt::create([
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'exemplaire_id' => $exemplaire->id,
            'date_emprunt' => now()->subDays(3)->toDateString(),
            'date_retour_prevue' => now()->addDays(5)->toDateString(),
            'statut' => 'en cours',
        ]);
        $reservation = \App\Models\Reservation::create([
            'user_id' => $etudiant->id,
            'livre_id' => $livre->id,
            'date_reservation' => now()->toDateString(),
            'date_expiration' => now()->addDays(7)->toDateString(),
            'statut' => 'active',
            'position_file_attente' => 1,
        ]);
        $penalite = \App\Models\Penalite::create([
            'user_id' => $etudiant->id,
            'type' => 'retard',
            'montant' => 500,
            'statut' => 'impayee',
            'motif' => 'Test',
        ]);
        $auteur = \App\Models\Auteur::factory()->create();
        $editeur = \App\Models\Editeur::factory()->create();
        $categorie = \App\Models\Categorie::first();
        $role = \App\Models\Role::first();
        $journal = \App\Models\JournalActivite::create([
            'action' => 'creation', 'module' => 'test',
            'description' => 'Entrée de test',
        ]);

        $parametres = [
            'livre' => $livre->id,
            'exemplaire' => $exemplaire->id,
            'emprunt' => $emprunt->id,
            'reservation' => $reservation->id,
            'penalite' => $penalite->id,
            'user' => $etudiant->id,
            'etudiant' => $etudiant->id,
            'auteur' => $auteur->id,
            'editeur' => $editeur->id,
            'categorie' => $categorie->id,
            'role' => $role->id,
            'journal' => $journal->id,
            'rapport' => 'emprunts',
            'niveau' => 'bibliotheques',
            'id' => 1,
            'document' => 1,
            'annee' => 1,
            'renouvellement' => 1,
            'token' => 'jeton-test',
        ];

        $ignorees = ['livres.download', 'livres.stream', 'documents.consulter',
            'documents.telecharger', 'exemplaires.etiquette', 'logout'];

        $echecs = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $nom = $route->getName();

            if (! $nom || in_array($nom, $ignorees, true)
                || str_starts_with($route->uri(), 'password')
                || str_starts_with($route->uri(), 'email')
                || in_array($route->uri(), ['up', 'login', 'register'], true)) {
                continue;
            }

            $uri = $route->uri();
            foreach ($route->parameterNames() as $parametre) {
                if (! isset($parametres[$parametre])) {
                    continue 2; // paramètre inconnu : route ignorée
                }
                $uri = preg_replace('/\{'.$parametre.'\??\}/', (string) $parametres[$parametre], $uri);
            }

            $reponse = $this->actingAs($admin)->get('/'.ltrim($uri, '/'));

            if ($reponse->status() >= 500) {
                $echecs[] = $nom.' ('.$uri.') → '.$reponse->status();
            }
        }

        $this->assertSame([], $echecs, "Pages en erreur :\n".implode("\n", $echecs));
    }
}
