<?php

require_once 'vendor/autoload.php';

// Boot Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Test d'authentification COMPLET ===\n\n";

// Mot de passe à tester
$password = 'yaya1539';
$storedHash = '$2y$12$rxHZQiU/YTye477Zmk6ZGOUCENWjmOYom4lAdnojImgXGOac12dZu';

echo "Mot de passe : $password\n";
echo "Hash stocké : $storedHash\n\n";

// Test 1: Vérification avec Hash::check de Laravel
$isValidLaravel = Hash::check($password, $storedHash);
echo "Hash::check() Laravel : " . ($isValidLaravel ? 'VALIDE' : 'INVALIDE') . "\n";

// Test 2: Vérification avec password_verify PHP natif
$isValidNative = password_verify($password, $storedHash);
echo "password_verify() PHP : " . ($isValidNative ? 'VALIDE' : 'INVALIDE') . "\n\n";

// Test 3: Chercher l'utilisateur dans la base de données
echo "=== Recherche dans la base de données ===\n";
$users = User::all();

foreach ($users as $user) {
    echo "Utilisateur : {$user->name} ({$user->email})\n";
    echo "Hash dans la BDD : " . substr($user->password, 0, 30) . "...\n";
    
    // Test 4: Vérification avec le mot de passe de l'utilisateur
    $isValidUser = Hash::check($password, $user->password);
    echo "Hash::check() avec ce mot de passe : " . ($isValidUser ? 'VALIDE' : 'INVALIDE') . "\n";
    
    // Test 5: Tentative d'authentification Laravel
    $credentials = [
        'email' => $user->email,
        'password' => $password
    ];
    
    echo "Test Auth::attempt() avec {$user->email} : ";
    
    // Simuler la tentative d'authentification
    $userFromDB = User::where('email', $user->email)->first();
    if ($userFromDB && Hash::check($password, $userFromDB->password)) {
        echo "SUCCÈS\n";
    } else {
        echo "ÉCHEC\n";
    }
    echo "---\n";
}

// Test 6: Créer un utilisateur de test avec le hash fourni
echo "\n=== Test avec hash fourni ===\n";
$testUser = new \stdClass();
$testUser->password = $storedHash;

$isValidTestHash = Hash::check($password, $testUser->password);
echo "Vérification directe du hash fourni : " . ($isValidTestHash ? 'VALIDE' : 'INVALIDE') . "\n";

// Test 7: Vérifier le format du hash
$hashInfo = password_get_info($storedHash);
echo "Informations sur le hash :\n";
echo "- Algorithme : " . $hashInfo['algoName'] . "\n";
echo "- Coût (cost) : " . $hashInfo['options']['cost'] . "\n";

?>
