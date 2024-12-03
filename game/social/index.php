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
$stmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user2_id = :user_id AND accepted = 0');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$nbPendingRequests = $stmt->fetchColumn();

// Récupérer les meilleurs joueurs (avec le + de 'eggs' sur la table scores)
$stmt = $pdo->prepare('
    SELECT u.username, u.displayname, u.id, s.eggs 
    FROM users u 
    JOIN scores s ON u.id = s.user_id 
    ORDER BY s.eggs DESC 
    LIMIT 10
');
$stmt->execute();
$best_players = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <a href="friends_list" class="button">
            Mes amis 
            <?php if ($nbPendingRequests > 0) echo '<span style="color: gold;">(' . htmlspecialchars($nbPendingRequests) . ')</span>';?>
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
        </form>


        <div style ="  width: 300px;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 10px;
  background-color: #fff;">
        <h2>Podium ALL-TIME</h2>
        <?php if (!empty($best_players)): ?>
            <ul class="friends-list">
                <?php foreach ($best_players as $player): ?>
                    <a href="player?username=<?php echo htmlspecialchars($player['username']);?>" style="no-link">
                        <li>
                        <p><?php echo htmlspecialchars($player['eggs']) ?> oeufs</p>
                        <img src="<?php echo getProfilePicture($player['id']);?>" alt="Icone joueur" class="player-icon">
                        <strong><?php echo htmlspecialchars($player['displayname']); ?></strong>
                        </li>
                    </a>

                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Pourquoi c'est aussi vide!?</p>
        <?php endif; ?>
        </div>

    </div>
</body>
</html>
