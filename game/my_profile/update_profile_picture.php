<?php
session_start();
require_once '../profile_picture.php'; // Connexion à la base de données

if (!isset($_SESSION['user_id']) || !isset($_POST['icon_id'])) {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
    exit();
}

$userId = $_SESSION['user_id'];
$iconId = $_POST['icon_id'];

// Vérifier si l'icône existe
$stmt = $pdo->prepare('SELECT image_url FROM profile_icons WHERE id = :icon_id');
$stmt->execute(['icon_id' => $iconId]);
$icon = $stmt->fetch(PDO::FETCH_ASSOC);

if ($icon) {
    // Mettre à jour l'image de profil de l'utilisateur
    $updateStmt = $pdo->prepare('UPDATE users SET profile_picture = :image_url WHERE id = :user_id');
    $updateStmt->execute(['image_url' => $icon['image_url'], 'user_id' => $userId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Image non trouvée.']);
}
?>
