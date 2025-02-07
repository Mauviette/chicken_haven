<?php

require_once '../database/db_connect.php';

session_start();

// Récupérer et décoder les données JSON envoyées
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Vérifier si egg_id est présent
if (!isset($input['egg_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'egg_id manquant',
        'received' => $inputJSON // Ajouté pour voir ce qui est réellement reçu
    ]);
    exit();
}

$egg_id = intval($input['egg_id']); // Sécuriser l'ID

// Récupérer les probabilités de chaque rareté pour cet œuf
$stmt = $pdo->prepare("SELECT probability_common, probability_rare, probability_epic, probability_legendary FROM openable_eggs WHERE id = ?");
$stmt->execute([$egg_id]);
$probabilities = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$probabilities) {
    echo json_encode(['success' => false, 'error' => 'Œuf non trouvé']);
    exit();
}

// Récupérer les poules contenues dans cet œuf avec leur rareté
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.image_url, c.rarity
    FROM egg_contents ec
    JOIN chickens c ON ec.chicken_id = c.id
    WHERE ec.egg_id = ?
");
$stmt->execute([$egg_id]);
$chickens = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Associer les probabilités aux poules selon leur rareté
// Compter le nombre de poules par rareté dans l'œuf
$rarityCounts = [
    'common' => 0,
    'rare' => 0,
    'epic' => 0,
    'legendary' => 0
];

foreach ($chickens as $chicken) {
    if (isset($rarityCounts[$chicken['rarity']])) {
        $rarityCounts[$chicken['rarity']]++;
    }
}

// Calculer les probabilités ajustées
foreach ($chickens as &$chicken) {
    $rarity = $chicken['rarity'];
    if (isset($probabilities["probability_{$rarity}"]) && $rarityCounts[$rarity] > 0) {
        // On divise la probabilité totale par le nombre de poules de cette rareté
        $chicken['probability'] = round((($probabilities["probability_{$rarity}"] / $rarityCounts[$rarity]) * 100), 2);
    } else {
        $chicken['probability'] = 0; // Sécurité
    }
}


// Vérifier chaque poule si l'utilisateur n'a pas encore la poules, sinon l'url sera mystery.png et le nom sera ???

$user_id = $_SESSION['user_id'];

foreach ($chickens as &$chicken) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_chickens WHERE user_id = ? AND chicken_id = ?");
    $stmt->execute([$user_id, $chicken['id']]);
    $hasChicken = $stmt->fetchColumn() > 0;

    if (!$hasChicken) {
        $chicken['name'] = '???';
        $chicken['image_url'] = 'mystery.png';
    }
}


// Supprimer la référence pour éviter des problèmes avec d'autres boucles
unset($chicken);


echo json_encode(['success' => true, 'chickens' => $chickens]);
?>
