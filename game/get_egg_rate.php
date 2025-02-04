<?php
session_start();
require_once '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['eggsPerSecond' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$eggsPerSecond = 0;

// Poule noire
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM incubators 
    INNER JOIN chickens ON incubators.chicken_id = chickens.id
    WHERE incubators.user_id = :user_id AND chickens.name = 'Poule noire'
");
$stmt->execute(['user_id' => $user_id]);
$blackChickenCount = $stmt->fetchColumn();

$eggsPerSecond += 0.1 * $blackChickenCount;

// Canard
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM incubators 
    INNER JOIN chickens ON incubators.chicken_id = chickens.id
    WHERE incubators.user_id = :user_id AND chickens.name = 'Canard'
");
$stmt->execute(['user_id' => $user_id]);
$duckCount = $stmt->fetchColumn();

$eggsPerSecond += 1 * $duckCount;



echo json_encode(['eggsPerSecond' => $eggsPerSecond]);
?>
