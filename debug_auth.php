<?php

require_once 'vendor/autoload.php';

// Boot Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "=== Test d'authentification Laravel ===\n\n";

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
$user = User::where('email', 'like', '%yaya%')->first();

if ($user) {
    echo "Utilisateur trouvé : {$user->name} ({$user->email})\n";
    echo "Hash dans la BDD : " . substr($user->password, 0, 20) . "...\n";
    
    // Test 4: Vérification avec le mot de passe de l'utilisateur
    $isValidUser = Hash::check($password, $user->password);
    echo "Hash::check() avec BDD : " . ($isValidUser ? 'VALIDE' : 'INVALIDE') . "\n";
    
    // Test 5: Tentative d'authentification Laravel
    echo "\n=== Test d'authentification ===\n";
    $credentials = [
        'email' => $user->email,
        'password' => $password
    ];
    
    if (Auth::attempt($credentials)) {
        echo "Auth::attempt() : SUCCÈS\n";
    } else {
        echo "Auth::attempt() : ÉCHEC\n";
    }
} else {
    echo "Aucun utilisateur trouvé avec l'email contenant 'yaya'\n";
    
    // Afficher tous les utilisateurs pour débogage
    echo "\n=== Liste des utilisateurs ===\n";
    $users = User::all();
    foreach ($users as $u) {
        echo "- {$u->name} ({$u->email})\n";
    }
}

?>
