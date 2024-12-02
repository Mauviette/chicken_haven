<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /chicken_haven/index');
    exit();
}

require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_displayname = trim($_POST['newDisplayName']);

    if (empty($new_displayname)) {
        header("Location: /chicken_haven/game/my_profile/index?error=1");
        exit();
    }

    // Vérifie que le nom d'affichage fait de 4 à 20 caractères
    if (strlen($new_displayname) < 2 || strlen($new_displayname) > 30) {
        header("Location: /chicken_haven/game/my_profile/index?error=2");
        exit();
    }

    $username = $_SESSION['username'];

    if (empty($errors)) {
        try {
            // Préparer et exécuter la requête de mise à jour
            $stmt = $pdo->prepare("UPDATE users SET displayname = :new_displayname WHERE username = :username");
            $stmt->bindParam(':new_displayname', $new_displayname, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Mise à jour réussie, actualiser la session
                $_SESSION['displayname'] = $new_displayname;
                header('Location: /chicken_haven/game/my_profile/index?success=1');
                exit();
            } else {
                echo "Erreur lors de la mise à jour.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}

header('Location: ../game/my_profile');
exit();
?>
