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

// Refuser la demande d'ami (supprimer la demande)
$deleteStmt = $pdo->prepare('DELETE FROM friends WHERE user1_id = :targetUser AND user2_id = :currentUser AND accepted = 0');
$deleteStmt->execute(['targetUser' => $targetUserId, 'currentUser' => $currentUserId]);

header('Location: ../player.php?username=' . urlencode($targetUsername));
exit();
