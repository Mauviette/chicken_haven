<?php
session_start();


?>


<script>
    function updateSession() {
        fetch('/scripts/update_session.php')
            .then(response => response.text())
            .then(data => console.log('Session mise à jour'));
    }

    // Met à jour toutes les 30 secondes
    setInterval(updateSession, 30000);

    // Appel initial dès le chargement de la page
    updateSession();
</script>


<?php $sessions = json_decode(file_get_contents(__DIR__ . '/../session/sessions.json'), true); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Chicken Haven</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/resources/images/login.png" type="image/x-icon">

</head>
<body>
    <div class="form-container">
        <?php if (!isset($_SESSION['username'])): ?>
            <h1>Bienvenue sur Chicken Haven</h1>
            <p><?php echo $sessions ? count($sessions) : 0; ?> joueurs en ligne</p>

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

            <form method="POST" action="try_login.php" class="form">
                <div>
                    <label for="username">Nom d'utilisateur</label><br>
                    <input type="text" name="username" id="username" required>
                </div>
                <div>
                    <label for="password">Mot de passe</label><br>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Se connecter</button>
            </form>
            <a href="register.php">S'inscrire</a>
            <br><br>
        <?php else: 
            header("Location: /game/main/index.php"); 
        endif; ?>
    </div>
</body>
</html>

