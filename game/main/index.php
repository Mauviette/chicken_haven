<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index");
    exit();
}

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
    <link rel="icon" href="/chicken_haven/resources/images/game.png" type="image/x-icon">
    <style>
        .egg-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }

        .score {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

      @keyframes bounce {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
      } 

    .egg {
        width: 150px;
        height: 200px;
        background: url('/chicken_haven/resources/images/egg.png') no-repeat center center;
        background-size: contain;
        cursor: pointer;
    }

    .egg.bounce {
        animation: bounce 0.25s ease-in-out;
    }

    
    @keyframes fall {
        0% {
            transform: translate(0, 0) rotate(var(--start-rotation));
            opacity: 1;
        }
        30% {
            transform: translate(calc(var(--random-x) * 30px), -30px) rotate(calc(var(--start-rotation) + 45deg));
        }
        100% {
            transform: translate(calc(var(--random-x) * 100px), 200px) rotate(calc(var(--start-rotation) + 360deg));
            opacity: 0;
        }
    }

    .floating-text {
        position: absolute;
        font-size: 20px;
        font-weight: bold;
        color: #ffcc00;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        opacity: 1;
        animation: flyUp 1s ease-out forwards;
        pointer-events: none;
        user-select: none;
    }

    @keyframes flyUp {
        0% {
            transform: translateY(0);
            opacity: 1;
        }
        100% {
            transform: translateY(-50px);
            opacity: 0;
        }
    }


    .egg-fragment {
        position: absolute;
        width: 20px;
        height: 20px;
        background: url('/chicken_haven/resources/images/egg_fragment.png') no-repeat center center;
        background-size: cover;
        pointer-events: none; /* Empêche les fragments d'intercepter les clics */
        animation: fall 2s ease-out forwards;
    }


    </style>
</head>
<body>

  <?php require_once "../bars.php"; ?>
    <div class="main-container">
        <div class="form-container">
            <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['displayname']); ?></h1>
            <br><br>

            <div class="egg-container">
                <div class="score" id="score"><?php echo number_format($currentScore); ?> œufs</div>
                <div class="egg" id="egg"></div>
            </div>
            <br><br>
        </div>
    </div>


    <!-- Javascript -->
    <script>
document.getElementById('egg').addEventListener('click', function(event) {
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
                document.getElementById('score').textContent = response.newScore + ' œufs';

                // Animation du "+score"
                clickAnimation(event, response.increment);
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
