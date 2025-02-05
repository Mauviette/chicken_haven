<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /index.php");
    exit();
}

require_once '../../../database/db_connect.php';

// Récupérer l'ID de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $_SESSION['username']]);
$currentUserId = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id'])) {
    $friendId = $_POST['friend_id'];

    // Annuler la demande d'ami
    $stmt = $pdo->prepare('DELETE FROM friends WHERE user1_id = :currentUser AND user2_id = :friendId AND accepted = 0');
    $stmt->execute([
        'currentUser' => $currentUserId,
        'friendId' => $friendId,
    ]);

    header('Location: ../friends_list');
    exit();
} else {
    header('Location: ../index.php');
    exit();
}
?>