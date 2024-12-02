<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /chicken_haven/index");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../../database/db_connect.php'; // Connexion à la base de données

    // Récupération des données
    $currentUsername = $_SESSION['username'];
    $targetUsername = $_POST['username'];

    try {
        // Récupération des IDs des utilisateurs
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        
        // ID de l'utilisateur actuel
        $stmt->execute(['username' => $currentUsername]);
        $user1 = $stmt->fetch();

        // ID de l'utilisateur cible
        $stmt->execute(['username' => $targetUsername]);
        $user2 = $stmt->fetch();

        if (!$user1 || !$user2) {
            header("Location: /chicken_haven/game/social/index?error=usernotfound");
            exit();
        }

        $user1_id = $user1['id'];
        $user2_id = $user2['id'];

        // Vérification si la relation existe déjà
        $checkStmt = $pdo->prepare('SELECT * FROM friends WHERE (user1_id = :user1 AND user2_id = :user2) OR (user1_id = :user2 AND user2_id = :user1)');
        $checkStmt->execute(['user1' => $user1_id, 'user2' => $user2_id]);
        
        if ($checkStmt->rowCount() > 0) {
            header("Location: /chicken_haven/game/social/player?username=" . urlencode($targetUsername) . "&error=alreadyfriends");
            exit();
        }

        // Insertion de la demande d'ami
        $insertStmt = $pdo->prepare('INSERT INTO friends (user1_id, user2_id, accepted) VALUES (:user1, :user2, false)');
        $insertStmt->execute(['user1' => $user1_id, 'user2' => $user2_id]);

        header("Location: /chicken_haven/game/social/player?username=" . urlencode($targetUsername) . "&success=requestsent");
        exit();

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
} else {
    header("Location: /chicken_haven/game/social/index");
    exit();
}
