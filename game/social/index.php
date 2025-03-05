<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /index.php");
    exit();
}

require_once '../../database/db_connect.php'; // Inclut la connexion à la base de données

// Récupérer l'ID de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $_SESSION['username']]);
$currentUserId = $stmt->fetchColumn();

// Vérifier les demandes d'amis en attente
$stmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user2_id = :user_id AND accepted = 0');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$nbPendingRequests = $stmt->fetchColumn();

// Récupérer les meilleurs joueurs (avec le + de 'eggs_earned_total' sur la table scores)
$stmt = $pdo->prepare('
    SELECT u.username, u.displayname, u.id, s.eggs_earned_total 
    FROM users u 
    JOIN scores s ON u.id = s.user_id 
    WHERE u.cheater = 0
    ORDER BY s.eggs_earned_total DESC 
    LIMIT 5
');
$stmt->execute();
$best_players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les meilleurs joueurs des dernières 24h
$stmt = $pdo->prepare('
    SELECT u.username, u.displayname, u.id, s.eggs_last_day
    FROM users u 
    JOIN scores s ON u.id = s.user_id 
    WHERE u.cheater = 0
    ORDER BY s.eggs_last_day DESC 
    LIMIT 5
');
$stmt->execute();
$best_players_last_day = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Récupérer le total des oeufs de tous les joueurs
$stmt = $pdo->prepare('SELECT SUM(eggs_earned_total) FROM scores');
$stmt->execute();
$total_eggs = $stmt->fetchColumn();

//Récupérer le total des oeufs d'aujourd'hui de tous les joueurs
$stmt = $pdo->prepare('SELECT SUM(eggs_last_day) FROM scores');
$stmt->execute();
$total_eggs_last_day = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social - Chicken Haven</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
</head>
<body>


    <?php require_once "../bars.php"; ?>

    <div class="main-container">
        <card class="form-container" style="
            max-height: 50000px;">
            <h1>Social</h1>

            <!-- Affiche un (!) si des demandes d'amis sont en attente -->
            <a href="friends_list.php" class="button">
                Mes amis 
                <?php if ($nbPendingRequests > 0) echo '<span style="color: gold;">(' . htmlspecialchars($nbPendingRequests) . ')</span>';?>
            </a>

            <form action="search_player.php" method="post">

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
            </form>

            <?php
            afficherPodium('Podium (total d\'oeufs)', $total_eggs, $best_players, $currentUserId, $pdo);
            afficherPodium('Podium (dernières 24 heures)', $total_eggs_last_day, $best_players_last_day, $currentUserId, $pdo);
            ?>
    </div>
</div>

</body>
</html>


<style>
</style>

<?php

function afficherPodium($title, $total_eggs, $players, $currentUserId, $pdo) {
    echo '<div class="friends-section">';
    echo '<h2>' . htmlspecialchars($title) . '</h2>';
    echo '<p>Total : ' . htmlspecialchars(number_format($total_eggs)) . ' oeufs</p>';
    if (!empty($players)) {
        echo '<ul class="friends-list" style="justify-content: space-between;">';
        $playerOnLeaderBoard = false;
        foreach ($players as $player) {
            if ($player['id'] == $currentUserId) {
                $playerOnLeaderBoard = true;
                echo '<a href="player.php?username=' . htmlspecialchars($player['username']) . '" style="no-link">';
                echo '<li style="background-color: #ccc;">';
                echo '<img src="/resources/images/nothing.png" alt="Nothing Icon" class="friend-icon" style="width: 4%; height: 4%;">';
                echo '<p style="flex: 1;">' . htmlspecialchars(number_format($player['eggs'])) . ' oeufs</p>';
                echo '<img src="' . getProfilePicture($player['id']) . '" alt="Icone joueur" class="player-icon">';
                echo '<strong style="flex: 1;">' . htmlspecialchars($player['displayname']) . '</strong>';
                echo '</li>';
                echo '</a>';
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE ((user1_id = :current_user_id AND user2_id = :player_id) OR (user1_id = :player_id AND user2_id = :current_user_id)) AND accepted = 1');
                $stmt->execute(['current_user_id' => $currentUserId, 'player_id' => $player['id']]);
                $isFriend = $stmt->fetchColumn() > 0;
                echo '<a href="player.php?username=' . htmlspecialchars($player['username']) . '">';
                echo '<li>';
                if ($isFriend) {
                    echo '<img src="/resources/images/friends.png" alt="Friend Icon" class="friend-icon" style="width: 4%; height: 4%;">';
                } else {
                    echo '<img src="/resources/images/nothing.png" alt="Nothing Icon" class="friend-icon" style="width: 4%; height: 4%;">';
                }
                echo '<p style="flex: 1;">' . htmlspecialchars(number_format($player['eggs'])) . ' oeufs</p>';
                echo '<img src="' . getProfilePicture($player['id']) . '" alt="Icone joueur" class="player-icon">';
                echo '<strong style="flex: 1;">' . htmlspecialchars($player['displayname']) . '</strong>';
                echo '</li>';
                echo '</a>';
            }
        }
        if (!$playerOnLeaderBoard) {
            $stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $currentUserId]);
            $eggs = $stmt->fetchColumn();
            echo '<a href="player.php?username=' . htmlspecialchars($_SESSION['username']) . '" style="no-link">';
            echo '<li style="background-color: #ccc;">';
            echo '<img src="/resources/images/nothing.png" alt="Nothing Icon" class="friend-icon" style="width: 4%; height: 4%;">';
            echo '<p style="flex: 1;">' . htmlspecialchars(number_format($eggs)) . ' oeufs</p>';
            echo '<img src="' . getProfilePicture($_SESSION['user_id']) . '" alt="Icone joueur" class="player-icon">';
            echo '<strong style="flex: 1;">' . htmlspecialchars($_SESSION['displayname']) . '</strong>';
            echo '</li>';
            echo '</a>';
        }
        echo '</ul>';
    } else {
        echo '<p>Pourquoi c\'est aussi vide!?</p>';
    }
    echo '</div>';
}
