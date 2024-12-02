<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Chicken Haven</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/chicken_haven/resources/images/login.png" type="image/x-icon">

</head>
<body>
    <div class="form-container">
        <?php if (!isset($_SESSION['username'])): ?>
            <h1>Bienvenue sur Chicken Haven</h1>
            <p>0 joueurs en ligne</p>

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

            <form method="POST" action="try_login" class="form">
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
            <a href="register">S'inscrire</a>
            <br><br>
        <?php else: 
            header("Location: /chicken_haven/game/main/index"); 
        endif; ?>
    </div>
</body>
</html>