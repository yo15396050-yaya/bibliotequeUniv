<?php

use App\Http\Controllers\AuteurController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalBookController;
use App\Http\Controllers\EditeurController;
use App\Http\Controllers\EmpruntController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\ExemplaireController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JournalActiviteController;
use App\Http\Controllers\LivreController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\PenaliteController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\RechercheController;
use App\Http\Controllers\RenouvellementController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes web
|--------------------------------------------------------------------------
| Organisation par module. L'autorisation fine est portée par les Policies
| et les Gates (voir AppServiceProvider) ; le middleware `role` ne sert qu'aux
| écrans strictement réservés à l'administration.
*/

/* ----------------------------------------------------------------------- *
 | Pages publiques (accessibles sans compte)
 * ----------------------------------------------------------------------- */
Route::get('/', function () {
    // Un usager déjà connecté arrive directement sur son espace.
    return Auth::check()
        ? redirect()->route('dashboard')
        : app(PageController::class)->accueil();
})->name('accueil');

Route::get('/aide', [PageController::class, 'aide'])->name('aide');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/guide', [PageController::class, 'guide'])->name('guide');
Route::get('/conditions-utilisation', [PageController::class, 'conditions'])->name('conditions');

// Authentification (laravel/ui) — l'inscription publique reste fermée :
// les comptes sont créés par la bibliothèque.
Auth::routes(['register' => false, 'verify' => true]);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {

    /* ----------------------------------------------------------------- *
     | Tableaux de bord & recherche
     * ----------------------------------------------------------------- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistiques', [DashboardController::class, 'statistiques'])->name('statistiques');

    Route::get('/recherche', [RechercheController::class, 'index'])->name('recherche');
    Route::get('/recherche/suggestions', [RechercheController::class, 'suggestions'])
        ->middleware('throttle:60,1')->name('recherche.suggestions');
    // Ancienne URL conservée.
    Route::get('/search', fn () => redirect()->route('recherche', request()->query()))->name('search');

    /* ----------------------------------------------------------------- *
     | Catalogue
     * ----------------------------------------------------------------- */
    Route::get('/catalogue', [LivreController::class, 'catalogue'])->name('catalogue');

    Route::get('/livres/{livre}/qrcode', [LivreController::class, 'generateQRCode'])->name('livres.qrcode');
    Route::get('/livres/{livre}/lire', [LivreController::class, 'lire'])->name('livres.lire');
    Route::resource('livres', LivreController::class);

    Route::resource('auteurs', AuteurController::class);
    Route::resource('editeurs', EditeurController::class);
    Route::resource('categories', CategorieController::class);

    /* ----------------------------------------------------------------- *
     | Exemplaires, rayons et emplacements
     * ----------------------------------------------------------------- */
    Route::get('/exemplaires/recherche', [ExemplaireController::class, 'rechercheParCode'])
        ->name('exemplaires.recherche');
    Route::get('/exemplaires/{exemplaire}/etiquette', [ExemplaireController::class, 'etiquette'])
        ->name('exemplaires.etiquette');
    Route::resource('exemplaires', ExemplaireController::class);

    Route::prefix('localisations')->name('localisations.')->group(function () {
        Route::get('/', [LocalisationController::class, 'index'])->name('index');
        Route::post('/{niveau}', [LocalisationController::class, 'store'])->name('store');
        Route::put('/{niveau}/{id}', [LocalisationController::class, 'update'])->name('update');
        Route::delete('/{niveau}/{id}', [LocalisationController::class, 'destroy'])->name('destroy');
    });

    /* ----------------------------------------------------------------- *
     | Documents numériques (accès contrôlé par policy)
     * ----------------------------------------------------------------- */
    Route::get('/livres/{livre}/read', [DigitalBookController::class, 'read'])->name('livres.read');
    Route::get('/livres/{livre}/stream', [DigitalBookController::class, 'stream'])->name('livres.stream');
    Route::get('/livres/{livre}/download', [DigitalBookController::class, 'download'])
        ->middleware('throttle:30,1')->name('livres.download');
    Route::post('/livres/{livre}/documents', [DigitalBookController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/consulter', [DigitalBookController::class, 'afficherDocument'])
        ->name('documents.consulter');
    Route::get('/documents/{document}/telecharger', [DigitalBookController::class, 'telechargerDocument'])
        ->middleware('throttle:30,1')->name('documents.telecharger');
    Route::delete('/documents/{document}', [DigitalBookController::class, 'destroy'])->name('documents.destroy');

    /* ----------------------------------------------------------------- *
     | Usagers
     * ----------------------------------------------------------------- */
    Route::resource('etudiants', EtudiantController::class)->parameters(['etudiants' => 'etudiant']);

    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles');
    Route::put('/users/{user}/mot-de-passe', [UserController::class, 'reinitialiserMotDePasse'])
        ->name('users.mot-de-passe');
    Route::resource('users', UserController::class);

    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('profile');
        Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
    });

    /* ----------------------------------------------------------------- *
     | Circulation : emprunts, retours, renouvellements
     * ----------------------------------------------------------------- */
    Route::get('/mes-emprunts', [EmpruntController::class, 'mesEmprunts'])->name('mes-emprunts');
    Route::get('/guichet-retour', [EmpruntController::class, 'guichetRetour'])->name('emprunts.guichet');
    Route::post('/emprunts/rappel-retard', [EmpruntController::class, 'rappelRetard'])->name('emprunts.rappel-retard');
    Route::post('/emprunts/{emprunt}/retour', [EmpruntController::class, 'retour'])->name('emprunts.retour');
    Route::post('/emprunts/{emprunt}/fiche', [EmpruntController::class, 'genererFiche'])->name('emprunts.fiche');
    Route::post('/emprunts/{emprunt}/rappel-mail', [EmpruntController::class, 'envoyerRappel'])->name('emprunts.rappel-mail');
    Route::post('/emprunts/{emprunt}/renouveler', [RenouvellementController::class, 'store'])->name('emprunts.renouveler');
    Route::resource('emprunts', EmpruntController::class)->except(['edit', 'update']);

    Route::get('/renouvellements', [RenouvellementController::class, 'index'])->name('renouvellements.index');
    Route::put('/renouvellements/{renouvellement}', [RenouvellementController::class, 'traiter'])
        ->name('renouvellements.traiter');

    /* ----------------------------------------------------------------- *
     | Réservations
     * ----------------------------------------------------------------- */
    Route::post('/reserver/{livre}', [ReservationController::class, 'reserver'])->name('reserver');
    Route::post('/reservations/{reservation}/annuler', [ReservationController::class, 'annuler'])
        ->name('reservations.annuler');
    Route::post('/reservations/{reservation}/notifier', [ReservationController::class, 'notifier'])
        ->name('reservations.notifier');
    Route::resource('reservations', ReservationController::class);

    /* ----------------------------------------------------------------- *
     | Pénalités
     * ----------------------------------------------------------------- */
    Route::post('/penalites/{penalite}/payer', [PenaliteController::class, 'payer'])->name('penalites.payer');
    Route::post('/penalites/{penalite}/annuler', [PenaliteController::class, 'annuler'])->name('penalites.annuler');
    Route::get('/penalites/{penalite}/recu', [PenaliteController::class, 'recu'])->name('penalites.recu');
    Route::resource('penalites', PenaliteController::class)->only(['index', 'create', 'store', 'show']);
    // Ancienne URL « amendes » conservée.
    Route::get('/amendes', fn () => redirect()->route('penalites.index'))->name('amendes.index');

    /* ----------------------------------------------------------------- *
     | Notifications
     * ----------------------------------------------------------------- */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recentes', [NotificationController::class, 'recentes'])->name('recentes');
        Route::post('/{id}/lue', [NotificationController::class, 'marquerLue'])->name('lue');
        Route::post('/toutes-lues', [NotificationController::class, 'toutMarquerLues'])->name('toutes-lues');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    /* ----------------------------------------------------------------- *
     | Rapports & exports
     * ----------------------------------------------------------------- */
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [RapportController::class, 'index'])->name('index');
        Route::get('/{rapport}', [RapportController::class, 'afficher'])->name('afficher');
    });

    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/inventaire', [ExportController::class, 'inventaire'])->name('inventaire');
        Route::get('/retards', [ExportController::class, 'retards'])->name('retards');
        Route::get('/etudiant/{etudiant}', [ExportController::class, 'etudiant'])->name('etudiant');
    });

    /* ----------------------------------------------------------------- *
     | Audit
     * ----------------------------------------------------------------- */
    Route::get('/audit', [JournalActiviteController::class, 'index'])->name('audit.index');
    Route::get('/audit/{journal}', [JournalActiviteController::class, 'show'])->name('audit.show');

    /* ----------------------------------------------------------------- *
     | Administration
     * ----------------------------------------------------------------- */
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [ParametreController::class, 'index'])->name('index');
        Route::put('/', [ParametreController::class, 'update'])->name('update');
        Route::get('/roles', [ParametreController::class, 'roles'])->name('roles');
        Route::put('/roles/{role}', [ParametreController::class, 'updateRole'])->name('roles.update');
        Route::get('/annees-academiques', [ParametreController::class, 'anneesAcademiques'])->name('annees');
        Route::post('/annees-academiques', [ParametreController::class, 'storeAnnee'])->name('annees.store');
        Route::put('/annees-academiques/{annee}/activer', [ParametreController::class, 'activerAnnee'])
            ->name('annees.activer');
        Route::post('/vider-cache', [ParametreController::class, 'viderCache'])->name('vider-cache');
    });
});
