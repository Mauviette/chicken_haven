<?php
session_start();

// Connexion à la base de données
require_once '../../database/db_connect.php';

// Récupérer le score actuel de l'utilisateur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$currentScore = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Haven</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
    <style>



    </style>
</head>

<body>

  <?php require_once "../bars.php"; ?>
    <div class="main-container">
        <div class="form-container">
            <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['displayname']); ?></h1>
            <br><br>

            <div class="egg-container">
                <div class="score main-score" id="score"><?php echo number_format($currentScore, 0, ',', ' '); ?> œufs</div>
                <div class="egg" id="egg"></div>
            </div>
            <br><br>
        </div>
    </div>


    <!-- Javascript -->
    <script>


let clicksPerSecond = 0;

document.getElementById('egg').addEventListener('click', function(event) {
    // Calculer les clics par seconde
    clicksPerSecond++;
    setTimeout(() => {
        clicksPerSecond--;
    }, 1000);

    if (clicksPerSecond > 20) {
        window.location.href = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    }

    const egg = document.getElementById('egg');

    // Ajouter la classe d'animation pour l'œuf
    egg.classList.add('bounce');

    // Créer un fragment d'œuf
    const fragment = document.createElement('div');
    fragment.classList.add('egg-fragment');

    // Générer une direction aléatoire pour le fragment
    const randomX = Math.random() * 2 - 1; // Valeur aléatoire entre -1 et 1
    const randomRotation = Math.floor(Math.random() * 360) + 'deg'; // Rotation initiale aléatoire

    // Appliquer les variables CSS personnalisées
    fragment.style.setProperty('--random-x', randomX);
    fragment.style.setProperty('--start-rotation', randomRotation);

    // Positionner le fragment à l'endroit du clic
    fragment.style.left = event.clientX + 'px';
    fragment.style.top = event.clientY + 'px';

    // Ajouter le fragment au body
    document.body.appendChild(fragment);

    // Retirer la classe bounce après l'animation
    setTimeout(() => {
        egg.classList.remove('bounce');
    }, 250);

    // Supprimer le fragment après la fin de l'animation
    fragment.addEventListener('animationend', () => {
        fragment.remove();
    });

    // Envoyer une requête AJAX à clicked.php pour mettre à jour le score
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'clicked.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            // Parse the JSON response
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Mettre à jour le score affiché
                const scoreElements = document.querySelectorAll('.score');
                scoreElements.forEach(element => {
                    element.textContent = new Intl.NumberFormat().format(response.newScore) + ' œufs';
                });

                // Animation du "+score"
                clickAnimation(event, response.increment);
            } else if (response.error === 'Click too fast') {
                console.log('Vous cliquez trop vite !');
            } else if (response.error === 'Cheating detected') {
                alert('Triche détectée ! Si vous continuez à tricher, votre compte passera en mode tricheur. \nDans ce mode, vous ne compterez plus dans le podium et une marque "tricheur" sera affichée sur votre profil.');
            } else if (response.error === 'Cheating detected, already alerted') {
                alert('Triche détectée ! Vous avez été marqué comme tricheur.');
            } else if (response.error === 'Cheating detected recently') {
                console.log('Triche détectée récemment. Ignorée. ' + response.lastCheatTime);
            }
        }
    };
    xhr.send();

});


function clickAnimation(event, increment) {
    // Création de l'élément du "+score"
    let floatingText = document.createElement("span");
    floatingText.textContent = `+${increment}`;
    floatingText.classList.add("floating-text");

    // Positionner l'élément au niveau du clic
    floatingText.style.left = `${event.clientX}px`;
    floatingText.style.top = `${event.clientY}px`;

    document.body.appendChild(floatingText);

    // Supprimer l'élément après l'animation
    setTimeout(() => {
        floatingText.remove();
    }, 1000); // Correspond à la durée de l'animation
}



</script>

</body>
</html>
