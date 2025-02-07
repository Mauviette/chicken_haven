<?php
require_once '../database/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode invalide']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['egg_id'])) {
    echo json_encode(['success' => false, 'error' => 'egg_id manquant']);
    exit();
}

$user_id = $_SESSION['user_id'];
$egg_id = (int) $data['egg_id'];

try {
    $pdo->beginTransaction();

    // Récupérer les probabilités de l'œuf
    $stmt = $pdo->prepare("SELECT price, probability_common, probability_rare, probability_epic, probability_legendary FROM openable_eggs WHERE id = :egg_id");
    $stmt->execute(['egg_id' => $egg_id]);
    $probabilities = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$probabilities) {
        echo json_encode(['success' => false, 'error' => 'Oeuf non trouvé']);
        exit();
    }

    $egg_price = (int) $probabilities['price'];

    // Vérifier le solde en œufs du joueur
    $stmt = $pdo->prepare("SELECT eggs FROM scores WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $current_eggs = (int) $stmt->fetchColumn();
  
    if ($current_eggs < $egg_price) {
        echo json_encode(['success' => false, 'error' => 'Pas assez d\'œufs']);
        exit();
    }
  
    // Déduire le coût de l'œuf
    $stmt = $pdo->prepare("UPDATE scores SET eggs = eggs - :price WHERE user_id = :user_id");
    $stmt->execute(['price' => $egg_price, 'user_id' => $user_id]);
      
    // Récupérer les poules disponibles pour cet œuf
    $stmt = $pdo->prepare("
        SELECT c.id, c.name, c.image_url, c.rarity
        FROM egg_contents ec
        JOIN chickens c ON ec.chicken_id = c.id
        WHERE ec.egg_id = :egg_id
    ");
    $stmt->execute(['egg_id' => $egg_id]);
    $chickens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$chickens) {
        echo json_encode(['success' => false, 'error' => 'Aucune poule disponible']);
        exit();
    }

    // Calcul des chances par rareté
    $rarity_probabilities = [
        'common' => $probabilities['probability_common'],
        'rare' => $probabilities['probability_rare'],
        'epic' => $probabilities['probability_epic'],
        'legendary' => $probabilities['probability_legendary'],
    ];

    // Trier les poules par rareté
    $chickens_by_rarity = [];
    foreach ($chickens as $chicken) {
        $chickens_by_rarity[$chicken['rarity']][] = $chicken;
    }

    // Sélectionner une rareté basée sur les probabilités
    $rand = mt_rand(1, 100);
    $cumulative = 0;
    $selected_rarity = 'common';

    foreach ($rarity_probabilities as $rarity => $prob) {
        $cumulative += $prob * 100; // Convertir en pourcentage
        if ($rand <= $cumulative) {
            $selected_rarity = $rarity;
            break;
        }
    }

    // Sélectionner une poule aléatoire dans cette rareté
    if (!isset($chickens_by_rarity[$selected_rarity])) {
        echo json_encode(['success' => false, 'error' => 'Aucune poule trouvée pour la rareté sélectionnée']);
        exit();
    }
    $selected_chicken = $chickens_by_rarity[$selected_rarity][array_rand($chickens_by_rarity[$selected_rarity])];

    // Vérifier si le joueur possède déjà cette poule
    $stmt = $pdo->prepare("SELECT count FROM user_chickens WHERE user_id = :user_id AND chicken_id = :chicken_id");
    $stmt->execute(['user_id' => $user_id, 'chicken_id' => $selected_chicken['id']]);
    $existingChicken = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingChicken) {
        // Incrémenter la quantité si la poule est déjà possédée
        $stmt = $pdo->prepare("UPDATE user_chickens SET count = count + 1 WHERE user_id = :user_id AND chicken_id = :chicken_id");
        $stmt->execute(['user_id' => $user_id, 'chicken_id' => $selected_chicken['id']]);
    } else {
        // Ajouter la poule au joueur
        $stmt = $pdo->prepare("INSERT INTO user_chickens (user_id, chicken_id, count) VALUES (:user_id, :chicken_id, 1)");
        $stmt->execute(['user_id' => $user_id, 'chicken_id' => $selected_chicken['id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'chicken' => [
            'id' => $selected_chicken['id'],
            'name' => $selected_chicken['name'],
            'image_url' => $selected_chicken['image_url'],
            'rarity' => $selected_chicken['rarity']
        ],
        'newScore' => $current_eggs - $egg_price
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Erreur serveur', 'details' => $e->getMessage()]);
}
?>
