<?php
session_start();
include 'bars.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$displayname = $_SESSION['displayname'];
// Exemple de récupération :
//$email = "user@example.com";
//$date_inscription = "2024-01-01";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profdfdsfdsil - Chicken Haven</title>
    <link rel="icon" href="images/game.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>

    <div class="form-container">
        <h1>Mon profil</h1>
        <p><strong><?php echo htmlspecialchars($displayname); ?></strong> (<strong>@<?php echo htmlspecialchars($username); ?></strong>)</p>

        <img src="images/player_icon.png" alt="Profil" class="profile-icon-big">
        <br><br>
        
        
        <a href="logout.php">Se déconnecter</a><br><br>
    
    </div>
</body>
</html>
