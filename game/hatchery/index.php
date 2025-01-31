<?php
session_start();
require_once '../../database/db_connect.php'; // Connexion à la BDD

$user_id = $_SESSION['user_id'];

// Récupérer les poules possédées par l'utilisateur
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.image_url, uc.count, c.rarity
    FROM chickens c
    JOIN user_chickens uc ON c.id = uc.chicken_id
    WHERE uc.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$ownedChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les poules manquantes
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.image_url, c.rarity
    FROM chickens c
    WHERE c.id NOT IN (SELECT chicken_id FROM user_chickens WHERE user_id = :user_id)
");
$stmt->execute(['user_id' => $user_id]);
$missingChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Poules - Chicken Haven</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="form-container">

        <h1>Mes Poules</h1>

        <h2>Poules possédées</h2>
        <div class="chicken-container">
            <?php if (empty($ownedChickens)): ?>
                <p>Aucune poule obtenue pour l'instant.</p>
            <?php else: ?>
                <?php foreach ($ownedChickens as $chicken): ?>
                    <div class="chicken-card <?php echo htmlspecialchars($chicken['rarity']); ?>">
                        <img src="/chicken_haven/resources/images/chickens/<?php echo htmlspecialchars($chicken['image_url']); ?>.png" alt="<?php echo htmlspecialchars($chicken['name']); ?>">
                        <p><?php echo htmlspecialchars($chicken['name']); ?> (x<?php echo $chicken['count']; ?>)</p>
                        <span class="rarity-label"><?php echo ucfirst($chicken['rarity']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2>Poules manquantes</h2>
        <div class="chicken-container">
            <?php if (empty($missingChickens)): ?>
                <p>Bravo ! Tu as collecté toutes les poules ! 🎉</p>
            <?php else: ?>
                <?php foreach ($missingChickens as $chicken): ?>
                    <div class="chicken-card missing <?php echo htmlspecialchars($chicken['rarity']); ?>">
                        <img src="/chicken_haven/resources/images/chickens/mystery.png" alt="Poule mystère">
                        <p>???</p>
                        <span class="rarity-label"><?php echo ucfirst($chicken['rarity']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <style>
        .chicken-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .chicken-card {
            border: 2px solid green;
            padding: 10px;
            text-align: center;
        }
        .chicken-card img {
            width: 100px;
            height: 100px;
        }
        .missing {
            border: 2px solid red;
            opacity: 0.5;
        }
    </style>

</body>
</html>
