<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index");
    exit();
}

require_once '../../database/db_connect.php';

// Récupérer l'ID de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $_SESSION['username']]);
$currentUserId = $stmt->fetchColumn();

// Récupérer les demandes d'amis reçues
$pendingRequestsStmt = $pdo->prepare('
    SELECT u.id, u.username, u.displayname
    FROM friends f
    JOIN users u ON f.user1_id = u.id
    WHERE f.user2_id = :currentUser AND f.accepted = 0
');
$pendingRequestsStmt->execute(['currentUser' => $currentUserId]);
$pendingRequests = $pendingRequestsStmt->fetchAll();

// Récupérer la liste des amis
$friendsStmt = $pdo->prepare('
    SELECT u.id, u.username, u.displayname
    FROM friends f
    JOIN users u ON (f.user1_id = u.id OR f.user2_id = u.id)
    WHERE (f.user1_id = :currentUser OR f.user2_id = :currentUser) AND f.accepted = 1 AND u.id != :currentUser
');
$friendsStmt->execute(['currentUser' => $currentUserId]);
$friends = $friendsStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste d'amis - Chicken Haven</title>
    <link rel="stylesheet" href="player_profile.css">
    <link rel="icon" href="/chicken_haven/resources/images/game.png" type="image/x-icon">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="form-container">
        <h1>Mes amis</h1>

        <!-- Affichage des demandes d'amis reçues -->
        <h2>Demandes d'amis reçues</h2>
        <?php if (!empty($pendingRequests)): ?>
            <ul class="friends-list">
                <?php foreach ($pendingRequests as $request): ?>
                    <li>
                        <img src="/chicken_haven/resources/images/player_icon.png" alt="Icone joueur" class="player-icon">
                        <strong><?php echo htmlspecialchars($request['displayname']); ?></strong> (@<?php echo htmlspecialchars($request['username']); ?>)
                        <form action="requests/accept_friend.php" method="post" class="inline-form" class="no-display">
                            <input type="hidden" name="friend_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="accept-button" title="Accepter la demande"></button>
                        </form>
                        <form action="requests/reject_friend.php" method="post" class="inline-form" class="no-display">
                            <input type="hidden" name="friend_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="reject-button" title="Refuser la demande"></button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune demande d'amis reçue.</p>
        <?php endif; ?>

        <!-- Affichage des amis -->
        <h2>Liste d'amis</h2>
        <?php if (!empty($friends)): ?>
            <ul class="friends-list">
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <img src="/chicken_haven/resources/images/player_icon.png" alt="Icone joueur" class="player-icon">
                        <strong><?php echo htmlspecialchars($friend['displayname']); ?></strong> (@<?php echo htmlspecialchars($friend['username']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez pas encore d'amis.</p>
        <?php endif; ?>
    </div>
</body>
</html>
