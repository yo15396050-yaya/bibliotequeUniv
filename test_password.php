<?php

// Mot de passe en clair
$password = 'yaya1539';

// Hash bcrypt de la base de données
$storedHash = '$2y$12$rxHZQiU/YTye477Zmk6ZGOUCENWjmOYom4lAdnojImgXGOac12dZu';

echo "Test de vérification du mot de passe :\n";
echo "Mot de passe : $password\n";
echo "Hash stocké : $storedHash\n\n";

// Test 1: Vérifier si le mot de passe correspond au hash (avec fonction PHP native)
$isValid = password_verify($password, $storedHash);
echo "Résultat de password_verify() : " . ($isValid ? 'VALIDE' : 'INVALIDE') . "\n\n";

// Test 2: Vérifier si le hash est bien au format bcrypt
$isBcryptFormat = preg_match('/^\$2y\$/', $storedHash);
echo "Format bcrypt valide : " . ($isBcryptFormat ? 'OUI' : 'NON') . "\n\n";

// Test 3: Générer un nouveau hash pour comparer
$newHash = password_hash($password, PASSWORD_BCRYPT);
echo "Nouveau hash généré : $newHash\n";

// Test 4: Vérifier avec le nouveau hash
$isNewHashValid = password_verify($password, $newHash);
echo "Vérification avec nouveau hash : " . ($isNewHashValid ? 'VALIDE' : 'INVALIDE') . "\n\n";

// Test 5: Informations sur le hash stocké
$hashInfo = password_get_info($storedHash);
echo "Informations sur le hash stocké :\n";
print_r($hashInfo);

?>
