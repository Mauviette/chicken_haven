<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /chicken_haven/index');
    exit();
}

$username = $_GET['username'];

if ($username == $_SESSION['username']) {
    header('Location: /chicken_haven/game/my_profile/index');
    exit();
}

require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO

$stmt = $pdo->prepare('SELECT id, displayname FROM users WHERE username = :username');
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user) {
    $targetUserId = $user['id'];
    $displayname = $user['displayname'];

    // Récupérer l'ID de l'utilisateur connecté
    $currentStmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $currentStmt->execute(['username' => $_SESSION['username']]);
    $currentUser = $currentStmt->fetch();
    $currentUserId = $currentUser['id'];

    // Vérifier le statut de la relation
    $relationStmt = $pdo->prepare('SELECT * FROM friends WHERE (user1_id = :current AND user2_id = :target) OR (user1_id = :target AND user2_id = :current)');
    $relationStmt->execute(['current' => $currentUserId, 'target' => $targetUserId]);
    $relation = $relationStmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($username); ?> - Chicken Haven</title>    
    <link rel="stylesheet" href="player_profile.css">
    <link rel="icon" href="images/game.png" type="image/x-icon">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="form-container">
        <h1>Profil de <?php echo htmlspecialchars($displayname); ?></h1>
        <p><strong><?php echo htmlspecialchars($displayname); ?></strong> (<strong>@<?php echo htmlspecialchars($username); ?></strong>)</p>
        <img src="/chicken_haven/resources/images/player_icon.png" alt="Profil" class="profile-icon-big">
        <br><br>

        <?php if (!$relation): ?>
            <!-- Bouton pour envoyer une demande d'ami -->
            <form action="/chicken_haven/game/social/friend/add_friend.php" method="post" class="no-display">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <button type="submit">Ajouter en ami</button>
            </form>
        <?php elseif (!$relation['accepted'] && $relation['user1_id'] == $currentUserId): ?>
            <!-- Bouton pour annuler une demande envoyée -->
            <form action="/chicken_haven/game/social/friend/cancel_request.php" method="post" class="no-display">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <button type="submit">Annuler la demande</button>
            </form>
        <?php elseif (!$relation['accepted'] && $relation['user2_id'] == $currentUserId): ?>
            <!-- Boutons pour accepter ou refuser une demande reçue -->
            <form action="/chicken_haven/game/social/friend/accept_request.php" method="post" class="no-display">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <button type="submit">Accepter la demande</button>
            </form>
            <form action="/chicken_haven/game/social/friend/decline_request.php" method="post" class="no-display">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <button type="submit">Refuser la demande</button>
            </form>
        <?php elseif ($relation['accepted']): ?>
            <!-- Bouton pour supprimer un ami -->
            <form action="/chicken_haven/game/social/friend/remove_friend.php" method="post" class="no-display">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <button type="submit">Supprimer des amis</button>
            </form>
        <?php endif; ?>

        <br><br>
    </div>
</body>
</html>