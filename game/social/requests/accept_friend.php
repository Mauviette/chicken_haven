<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../index");
    exit();
}

require_once '../../../database/db_connect.php';

// Récupérer l'ID de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $_SESSION['username']]);
$currentUserId = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id'])) {
    $friendId = $_POST['friend_id'];

    // Accepter la demande d'ami
    $stmt = $pdo->prepare('UPDATE friends SET accepted = 1 WHERE user1_id = :targetUser AND user2_id = :currentUser AND accepted = 0');
    $stmt->execute(['targetUser' => $friendId, 'currentUser' => $currentUserId]);

    header('Location: ../friends_list.php');
    exit();
} else {
    header('Location: ../index');
    exit();
}
