<?php
session_start();
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Haven</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="form-container">
        <?php if (!isset($_SESSION['username'])): ?>
            <h1>Bienvenue sur Chicken Haven</h1>

            <?php if (isset($_GET['error'])): ?>
                <p class="error">
                    <?php
                    if ($_GET['error'] == 1) {
                        echo "Nom d'utilisateur ou mot de passe incorrect.";
                    } elseif ($_GET['error'] == 2) {
                        echo "Veuillez remplir tous les champs.";
                    }
                    ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="connexion/login.php" class="form">
                <div>
                    <label for="username">Nom d'utilisateur :</label><br>
                    <input type="text" name="username" id="username" required>
                </div>
                <div>
                    <label for="password">Mot de passe :</label><br>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Se connecter</button>
            </form>
            <a href="connexion/register_page.php">S'inscrire</a>
        <?php else: ?>
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']); ?> !</h1>
            <a href="game/main/main.php">Accéder au jeu</a>
            <a href="scripts/logout.php">Se déconnecter</a>
        <?php endif; ?>
    </div>
</body>
</html>
