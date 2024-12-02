<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /chicken_haven/index");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO
    
    $searchedUsername = trim($_POST['username']);
    
    try {
        // Requête préparée pour vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->bindParam(':username', $searchedUsername, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Si l'utilisateur existe, redirige vers sa page de profil
            header("Location: /chicken_haven/game/social/player?username=" . urlencode($searchedUsername));
            exit();
        } else {
            // Si l'utilisateur n'existe pas, afficher un message d'erreur
            header("Location: index.php?error=notfound");
            exit();
        }
    } catch (PDOException $e) {
        // Gestion des erreurs de connexion ou d'exécution
        echo "Erreur : " . $e->getMessage();
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
