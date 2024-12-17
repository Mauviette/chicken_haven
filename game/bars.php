<?php
require_once '../../database/db_connect.php'; // Connexion à la base de données
require_once 'profile_picture.php';

// Vérifier les demandes d'amis si on n'est pas sur la page friends_list
$stmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user2_id = :user_id AND accepted = 0');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$nbPendingRequests = $stmt->fetchColumn();

$notification = ($nbPendingRequests > 0) ? ' <span style="color: gold;">' . $nbPendingRequests . ' </span>' : '';

echo '<!-- Barre de navigation -->
    <div class="navbar">
    <div class="profile-section">
        <a href="/chicken_haven/game/my_profile/index" class="profile-link">
        <img src=" ' . getProfilePicture($_SESSION['user_id']) . '" alt="Profil" class="profile-icon">
        <span class="username">' . htmlspecialchars($_SESSION['displayname']) . '</span>
        </a>
    </div>
    </div>

    <!-- Barre latérale -->
    <div class="sidebar" style="display: flex; justify-content: center;text-align: center;">
    <ul>
        <li><a href="/chicken_haven/game/main/index">Accueil</a></li>
        <li><a href="/chicken_haven/game/hatchery/index">Couvoir</a></li>
        <li><a href="/chicken_haven/game/shop/index">Boutique</a></li>
        <li><a href="/chicken_haven/game/social/index">Social ' . $notification . '</a></li>
    </ul>
    </div>

    <!-- Barre latérale droite
    <div class="sidebar-right" style="display: flex; justify-content: center;text-align: center; ">
    <ul>
        <li><a href="/chicken_haven/game/settings/index">Paramètres</a></li>
        <li><a href="/chicken_haven/game/logout.php">Déconnexion</a></li>
    </ul>
    </div> -->
    
    ';
?>