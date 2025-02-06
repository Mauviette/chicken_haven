<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /index.php');
    exit();
}


$username = $_GET['username'];

if ($username == $_SESSION['username']) {
    echo("<script>console.log('Viewing own profile');</script>");
    header('Location: /game/my_profile/index.php');
    exit();
}

echo("<script>console.log('Viewing " . $username . "\'s profile as " . $_SESSION['username'] . " ');</script>");

require_once '../../database/db_connect.php'; // Connexion à la base de données via PDO
require_once '../profile_picture.php'; // Connexion à la base de données via PDO

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

    // Récupérer le score actuel de l'utilisateur
    $stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $targetUserId]);
    $eggs = $stmt->fetchColumn();
    
    // Vérifier si l'utilisateur est un tricheur
    $stmt = $pdo->prepare('SELECT cheater FROM users WHERE id = :user_id');
    $stmt->execute(['user_id'=> $targetUserId]);
    $cheater = $stmt->fetchColumn();
    
    //Obtenir le nombre de poules sur le jeu
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM chickens');
    $stmt->execute();
    $total_chickens = $stmt->fetchColumn();

    //Obtenir le nombre de poules de l'utilisateur (de la table user_chickens, vérifier par rapport à user_id)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_chickens WHERE user_id = :user_id');
    $stmt->execute(['user_id'=> $targetUserId]);
    $chickens_obtained = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($username); ?> - Chicken Haven</title>    
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="<?php echo htmlspecialchars(getProfilePicture($targetUserId))?>" type="image/x-icon">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="main-container">
        <div class="form-container">
            <h1>Profil de <?php echo htmlspecialchars($displayname); ?></h1>
            <p><strong><?php echo htmlspecialchars($displayname); ?></strong> (<strong>@<?php echo htmlspecialchars($username); ?></strong>)</p>
            <img src="<?php echo getProfilePicture($targetUserId);?>" alt="Profil" class="profile-icon-big">
            <?php if ($cheater) { echo '<p style="color: red;"><b>Tricheur</b></p>'; }?>

            <br><br>

            <?php if ($cheater): ?>
            <?php endif; ?>

            <?php if (!$relation): ?>
                <!-- Bouton pour envoyer une demande d'ami -->
                <form action="/game/social/friend/add_friend.php" method="post" class="no-display">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit">Ajouter en ami</button>
                </form>
            <?php elseif (!$relation['accepted'] && $relation['user1_id'] == $currentUserId): ?>
                <!-- Bouton pour annuler une demande envoyée -->
                <form action="/game/social/friend/cancel_request.php" method="post" class="no-display">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit">Annuler la demande</button>
                </form>
            <?php elseif (!$relation['accepted'] && $relation['user2_id'] == $currentUserId): ?>
                <!-- Boutons pour accepter ou refuser une demande reçue -->
                <form action="/game/social/friend/accept_request.php" method="post" class="no-display">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit">Accepter la demande</button>
                </form>
                <form action="/game/social/friend/decline_request.php" method="post" class="no-display">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit">Refuser la demande</button>
                </form>
            <?php elseif ($relation['accepted']): ?>
                <!-- Bouton pour supprimer un ami -->
                <form action="/game/social/friend/remove_friend.php" method="post" class="no-display">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit">Supprimer des amis</button>
                </form>
            <?php endif; ?>

            <br>

            <p>Nombre d'œufs : <strong><?php echo number_format($eggs); ?></strong></p>
            <p>Poules débloquées : <strong><?php echo $chickens_obtained; ?>/<?php echo $total_chickens; ?></strong></p>

            <br><br>
        </div>
    </div>
</body>
</html>
