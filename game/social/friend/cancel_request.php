<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /index.php');
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

// Supprimer la demande d'ami
$deleteStmt = $pdo->prepare('DELETE FROM friends WHERE user1_id = :currentUser AND user2_id = :targetUser AND accepted = 0');
$deleteStmt->execute(['currentUser' => $currentUserId, 'targetUser' => $targetUserId]);

header('Location: ../player.php?username=' . urlencode($targetUsername));
exit();
