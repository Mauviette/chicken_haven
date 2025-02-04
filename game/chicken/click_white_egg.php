<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'];

// Récupérer le nombre de poules blanches
$stmt = $pdo->prepare('SELECT COUNT(*) FROM incubators WHERE user_id = :user_id AND chicken_id = (SELECT id FROM chickens WHERE name = "Poule blanche")');
$stmt->execute(['user_id' => $user_id]);
$whiteChickenCount = $stmt->fetchColumn();

if ($whiteChickenCount > 0) {
    $eggReward = $whiteChickenCount * 50;

    // Ajouter les œufs au joueur
    $stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :eggs WHERE user_id = :user_id');
    $stmt->execute(['eggs' => $eggReward, 'user_id' => $user_id]);

    // Récupérer le nouveau score
    $stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $newScore = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'newScore' => $newScore, 'increment' => $eggReward]);
} else {
    echo json_encode(['success' => false]);
}
?>
