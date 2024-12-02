<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /chicken_haven/index');
    exit();
}

require_once '../../../database/db_connect.php';

$currentUsername = $_SESSION['username'];
$targetUsername = $_POST['username'];

// Récupérer les ID des utilisateurs
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $currentUsername]);
$currentUserId = $stmt->fetchColumn();

$stmt->execute(['username' => $targetUsername]);
$targetUserId = $stmt->fetchColumn();

// Accepter la demande d'ami
$updateStmt = $pdo->prepare('UPDATE friends SET accepted = 1 WHERE user1_id = :targetUser AND user2_id = :currentUser AND accepted = 0');
$updateStmt->execute(['targetUser' => $targetUserId, 'currentUser' => $currentUserId]);

header('Location: ../player?username=' . urlencode($targetUsername));
exit();
