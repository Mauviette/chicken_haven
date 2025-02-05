<?php
session_start();


header('Content-Type: application/json'); // Définit le type de contenu JSON

require_once '../../database/db_connect.php'; // Connexion à la base de données

// Vérifie que l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère l'ID de l'icône depuis la requête POST
    if (isset($_POST['icon_id']) && is_numeric($_POST['icon_id'])) {
        $icon_id = (int) $_POST['icon_id'];
        $user_id = $_SESSION['user_id'];

        try {
            // Vérifie que l'utilisateur possède bien le chicken_id
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM user_chickens WHERE chicken_id = :icon_id AND user_id = :user_id');
            $checkStmt->execute(['icon_id' => $icon_id, 'user_id' => $user_id]);
            $chickenExists = $checkStmt->fetchColumn();

            if ($chickenExists > 0) {
                // Met à jour l'icône de profil de l'utilisateur dans la base de données
                $stmt = $pdo->prepare('UPDATE users SET profile_icon_id = :icon_id WHERE id = :user_id');
                $stmt->execute(['icon_id' => $icon_id, 'user_id' => $user_id]);

                // Vérifie que la mise à jour a réussi
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Image de profil mise à jour avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aucune mise à jour effectuée.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'L\'utilisateur ne possède pas ce poulet, tricheur.']);
            }
        } catch (PDOException $e) {
            // En cas d'erreur SQL
            echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
        }
    } else {
        // Si les données POST sont manquantes ou invalides
        echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    }
} else {
    // Si la méthode HTTP n'est pas POST
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>
