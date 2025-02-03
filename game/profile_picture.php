<?php
require_once '../../database/db_connect.php'; // Connexion à la base de données

function getProfilePicture($user_id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT profile_icon_id FROM users WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $profile_icon_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT image_url FROM chickens WHERE id = :profile_icon_id');
    $stmt->execute(['profile_icon_id' => $profile_icon_id]);
    $url = $stmt->fetchColumn();
    return "/resources/images/chickens/" . $url . ".png";
}


?>


