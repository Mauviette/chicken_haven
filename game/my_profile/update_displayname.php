<?php
session_start();

header('Content-Type: application/json');

$banned_words = ['hitler', 'nigger', 'nigga', 'pédé', 'négro', 'bougnoul', 'laden'];


require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO

// Vérifie que l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_displayname = trim($_POST['newDisplayName']);

    if (empty($new_displayname)) {
        echo json_encode(['success' => false, 'message' => 'Le nom d\'affichage ne peut pas être vide.']);
        exit();
    }

    // Vérifie que le nom d'affichage fait de 4 à 20 caractères
    if (strlen($new_displayname) < 2 || strlen($new_displayname) > 30) {
        echo json_encode(['success' => false, 'message' => 'Le nom d\'affichage doit faire entre 2 et 30 caractères.']);
        exit();
    }

    //Vérifie que le nom d'affichage ne contient pas de mots bannis
    foreach ($banned_words as $word) {
        if (stripos(strtolower($new_displayname), strtolower($word)) !== false) {
            echo json_encode(['success' => false, 'message' => 'Le nom d\'affichage contient des mots interdits.']);
            exit();
        }
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
                echo json_encode(['success' => true, 'message' => 'Nom d\'affichage mis à jour avec succès.']);
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
