<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'];
$increment = json_decode(file_get_contents('php://input'), true)['increment'] ?? 0;

$stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_last_day = eggs_last_day + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

// Récupérer le nouveau score
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$newScore = $stmt->fetchColumn();

echo json_encode(['success' => true, 'newScore' => $newScore]);
