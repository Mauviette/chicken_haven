<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'];
$base_increment = 1;

// Vérifier si une poule rousse est dans un des 3 emplacements de couveuse
$stmt = $pdo->prepare('
    SELECT COUNT(*) 
    FROM incubators 
    WHERE user_id = :user_id 
    AND chicken_id = (SELECT id FROM chickens WHERE name = "Poule Rousse")
');
$stmt->execute(['user_id' => $user_id]);
$hasChickenInIncubator = $stmt->fetchColumn() > 0; // True si une poule rousse est dans une couveuse

// Si la poule rousse est en couveuse, récupérer le nombre de poules rousses possédées
$bonus = 0;
if ($hasChickenInIncubator) {
    $stmt = $pdo->prepare('
        SELECT count 
        FROM user_chickens 
        WHERE user_id = :user_id 
        AND chicken_id = (SELECT id FROM chickens WHERE name = "Poule Rousse")
    ');
    $stmt->execute(['user_id' => $user_id]);
    $chickenCount = $stmt->fetchColumn() ?: 0; // Si aucune poule rousse, on met 0

    // Appliquer l'effet de la poule rousse
    $bonus = $chickenCount * 1; // +1 œuf par clic par poule rousse
}

// Calcul du total des œufs gagnés
$increment = $base_increment + $bonus;

// Mettre à jour les œufs dans la base
$stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_last_day = eggs_last_day + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

// Récupérer le nouveau score
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$newScore = $stmt->fetchColumn();

echo json_encode(['success' => true, 'newScore' => $newScore, 'increment' => $increment]);
?>
