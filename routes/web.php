<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LivreController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EmpruntController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AmendeController;
use App\Http\Controllers\DigitalBookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirection de la racine
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Routes d'authentification (sans inscription publique)
Auth::routes(['register' => false]);

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistiques', [DashboardController::class, 'statistiques'])->name('statistiques');

    // CRUD des livres
    Route::resource('livres', LivreController::class);
    Route::get('/livres/{livre}/qrcode', [LivreController::class, 'generateQRCode'])->name('livres.qrcode');
    Route::get('/livres/{livre}/lire', [LivreController::class, 'lire'])->name('livres.lire');

    // Lecture et téléchargement numérique
    Route::get('/livres/{livre}/read', [DigitalBookController::class, 'read'])->name('livres.read');
    Route::get('/livres/{livre}/stream', [DigitalBookController::class, 'stream'])->name('livres.stream');
    Route::get('/livres/{livre}/download', [DigitalBookController::class, 'download'])->name('livres.download');
    
    // Catalogue public pour étudiants
    Route::get('/catalogue', [LivreController::class, 'catalogue'])->name('catalogue');

    // CRUD des étudiants
    Route::resource('etudiants', EtudiantController::class);
    
    // CRUD des emprunts
    Route::resource('emprunts', EmpruntController::class)->except(['edit', 'update']);
    Route::post('/emprunts/{emprunt}/retour', [EmpruntController::class, 'retour'])->name('emprunts.retour');
    Route::post('/emprunts/{emprunt}/fiche', [EmpruntController::class, 'genererFiche'])->name('emprunts.fiche');
    Route::post('/emprunts/{emprunt}/rappel-mail', [EmpruntController::class, 'envoyerRappel'])->name('emprunts.rappel-mail');
    Route::post('/emprunts/rappel-retard', [EmpruntController::class, 'rappelRetard'])->name('emprunts.rappel-retard');
    
    // Emprunts de l'utilisateur connecté
    Route::get('/mes-emprunts', [EmpruntController::class, 'mesEmprunts'])->name('mes-emprunts');

    // CRUD des réservations
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/{reservation}/annuler', [ReservationController::class, 'annuler'])->name('reservations.annuler');
    Route::post('/reserver/{livre}', [ReservationController::class, 'reserver'])->name('reserver');

    // Gestion des Amendes
    Route::get('/amendes', [AmendeController::class, 'index'])->name('amendes.index');
    Route::post('/amendes/{emprunt}/payer', [AmendeController::class, 'payer'])->name('amendes.payer');

    // Exportations PDF
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/inventaire', [ExportController::class, 'inventaire'])->name('inventaire');
        Route::get('/retards', [ExportController::class, 'retards'])->name('retards');
        Route::get('/etudiant/{id}', [ExportController::class, 'etudiant'])->name('etudiant');
    });

    // Gestion des utilisateurs (admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Rapports et statistiques
    Route::prefix('rapports')->group(function () {
        Route::get('/', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('/emprunts', [RapportController::class, 'rapportEmprunts'])->name('rapports.emprunts');
        Route::get('/livres', [RapportController::class, 'rapportLivres'])->name('rapports.livres');
        Route::get('/etudiants', [RapportController::class, 'rapportEtudiants'])->name('rapports.etudiants');
        Route::get('/retards', function () {
            return view('rapports.retards');
        })->name('rapports.retards');
    });

    // Gestion des catégories
    Route::get('/categories', function () {
        return view('categories.index');
    })->name('categories.index');

    // Paramètres du système
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () {
            return view('settings.index');
        })->name('index');
        
        Route::get('/general', function () {
            return view('settings.general');
        })->name('general');
        
        Route::get('/notifications', function () {
            return view('settings.notifications');
        })->name('notifications');
        
        Route::get('/security', function () {
            return view('settings.security');
        })->name('security');
    });

    // Sauvegarde
    Route::get('/backup', function () {
        return view('backup.index');
    })->name('backup.index');

    // Recherche
    Route::get('/search', function () {
        return view('search');
    })->name('search');

    // Routes pour le profil
Route::prefix('profile')->middleware(['auth'])->group(function () {
    Route::get('/', function() {
        return view('profile.edit', ['user' => Auth::user()]);
    })->name('profile');
    
    Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
});

// Déconnexion (accessible sans middleware auth)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');

})->name('logout');
});
