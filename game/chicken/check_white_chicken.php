<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasWhiteChicken' => false]);
    exit();
}

require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'];

// Vérifier si une poule blanche est dans un incubateur
$stmt = $pdo->prepare('
    SELECT COUNT(*) 
    FROM incubators 
    WHERE user_id = :user_id 
    AND chicken_id = (SELECT id FROM chickens WHERE name = "Poule blanche")
');
$stmt->execute(['user_id' => $user_id]);
$hasWhiteChicken = $stmt->fetchColumn() > 0;

echo json_encode(['hasWhiteChicken' => $hasWhiteChicken]);
?>
