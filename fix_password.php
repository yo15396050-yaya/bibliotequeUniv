<?php

require_once 'vendor/autoload.php';

// Boot Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Correction du mot de passe dans la base de données ===\n\n";

// Récupérer l'utilisateur ouattara
$user = User::where('email', 'ouattara@gmail.com')->first();

if ($user) {
    echo "Utilisateur trouvé : {$user->name}\n";
    echo "Hash actuel : " . substr($user->password, 0, 30) . "...\n";
    
    // Mettre à jour avec le hash correct fourni
    $correctHash = '$2y$12$rxHZQiU/YTye477Zmk6ZGOUCENWjmOYom4lAdnojImgXGOac12dZu';
    
    // Nettoyer le hash
    $correctHash = trim($correctHash);
    
    // Mettre à jour directement dans la base de données
    User::where('id', $user->id)->update(['password' => $correctHash]);
    
    echo "Hash mis à jour avec succès !\n";
    echo "Nouveau hash : " . substr($correctHash, 0, 30) . "...\n\n";
    
    // Vérifier la mise à jour
    $updatedUser = User::find($user->id);
    echo "Vérification - Hash dans la BDD : " . substr($updatedUser->password, 0, 30) . "...\n";
    
    // Tester l'authentification
    $testPassword = 'yaya1539';
    $isValid = Hash::check($testPassword, $updatedUser->password);
    echo "Test d'authentification avec '$testPassword' : " . ($isValid ? 'SUCCÈS' : 'ÉCHEC') . "\n";
    
} else {
    echo "Utilisateur non trouvé !\n";
}

?>
