<?php
session_start();
require_once '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$increment = $data['increment'] ?? 0;

//Ajouter eggs_after_decimal à increment
$stmt = $pdo->prepare('SELECT eggs_after_decimal FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$increment += $stmt->fetchColumn();

//Arrondir increment et stocker le resultat dans $increment_after_decimal
$increment_after_decimal = $increment - floor($increment);
$increment = floor($increment);

// Mettre à jour les œufs dans la base
$stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_last_day = eggs_last_day + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_after_decimal = :increment_after_decimal WHERE user_id = :user_id');
$stmt->execute(['increment_after_decimal' => $increment_after_decimal, 'user_id' => $user_id]);

$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$newScore = $stmt->fetchColumn();

echo json_encode(['success' => true, 'newScore' => $newScore]);
?>
