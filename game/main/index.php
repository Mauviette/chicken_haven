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
    </style>
</head>
<body>

  <?php require_once "../bars.php"; ?>

    <div class="form-container">
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['displayname']); ?></h1>
        <br><br>

        <div class="egg-container">
            <div class="score" id="score"><?php echo $currentScore; ?> œufs</div>
            <div class="egg" id="egg"></div>
        </div>
        <br><br>
    </div>



    <!-- Javascript -->
    <script>
    document.getElementById('egg').addEventListener('click', function() {
        const egg = document.getElementById('egg');

        // Ajouter la classe pour l'animation
        egg.classList.add('bounce');

        // Envoyer la requête pour mettre à jour le score
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
                document.getElementById('score').textContent = data.newScore + ' œufs';
            } else {
                alert('Erreur lors de la mise à jour du score.');
            }

            // Retirer la classe d'animation une fois terminée
            setTimeout(() => {
                egg.classList.remove('bounce');
            }, 250); // Durée de l'animation (doit correspondre à la durée définie en CSS)
        });
    });
</script>

</body>
</html>
