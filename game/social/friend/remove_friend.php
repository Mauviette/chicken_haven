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

// Supprimer l'amitié
$deleteStmt = $pdo->prepare('DELETE FROM friends WHERE (user1_id = :currentUser AND user2_id = :targetUser) OR (user1_id = :targetUser AND user2_id = :currentUser) AND accepted = 1');
$deleteStmt->execute(['currentUser' => $currentUserId, 'targetUser' => $targetUserId]);

header('Location: ../player?username=' . urlencode($targetUsername));
exit();
