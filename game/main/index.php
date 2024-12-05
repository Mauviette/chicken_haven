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

    <div class="form-container">
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['displayname']); ?></h1>
        <br><br>

        <div class="egg-container">
            <div class="score" id="score"><?php echo number_format($currentScore); ?> œufs</div>
            <div class="egg" id="egg"></div>
        </div>
        <br><br>
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

    // Mettre à jour le score via une requête fetch
    fetch('update_score.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ increment: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('score').textContent = data.newScore.toLocaleString('en-US') + ' œufs';
        } else {
            alert('Erreur lors de la mise à jour du score.');
        }
    });
});
</script>

</body>
</html>
