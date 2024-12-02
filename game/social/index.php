<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index");
    exit();
}

require_once '../../database/db_connect.php'; // Inclut la connexion à la base de données

// Récupérer l'ID de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $_SESSION['username']]);
$currentUserId = $stmt->fetchColumn();

// Vérifier les demandes d'amis en attente
$pendingStmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user2_id = :currentUser AND accepted = 0');
$pendingStmt->execute(['currentUser' => $currentUserId]);
$pendingRequests = $pendingStmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social - Chicken Haven</title>
    <link rel="stylesheet" href="player_profile.css">
    <link rel="icon" href="/chicken_haven/resources/images/game.png" type="image/x-icon">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="form-container">
        <h1>Social</h1>

        <!-- Affiche un (!) si des demandes d'amis sont en attente -->
        <a href="friends_list.php" class="button">
            Mes amis <?php if ($pendingRequests > 0) echo '<span style="color: red;">(!)</span>'; ?>
        </a>

        <form action="search_player" method="post">

        <?php if (isset($_GET['error'])): ?>
            <p class="error" style="color: red; font-weight: bold;">
                <?php
                    if ($_GET['error'] == "notfound") {
                        echo "Joueur non trouvé.";
                    }
                ?>
            </p>
        <?php endif; ?>

        <p>Recherche de profil</p>
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <button type="submit">Rechercher</button>
    </div>
</body>
</html>
