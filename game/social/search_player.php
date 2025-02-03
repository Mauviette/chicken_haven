<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO
    
    $searchedUsername = strtolower(trim($_POST['username']));
    
    try {
        // Requête préparée pour vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->bindParam(':username', $searchedUsername, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: /game/social/player.php?username=" . urlencode($searchedUsername));
            exit();
        } else {
            // Si l'utilisateur n'existe pas, afficher un message d'erreur
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);

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
