<?php
session_start();
require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "Veuillez vous connecter.";
    exit;
}

// Récupérer le nombre d'œufs du joueur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$eggs = $stmt->fetchColumn();


// Récupérer les œufs ouvrables
$stmt = $pdo->prepare('SELECT id, name, image_url, price, buyable, probability_common, probability_rare, probability_epic, probability_legendary FROM openable_eggs WHERE limited = 0');
$stmt->execute();
$openable_eggs = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marché - Chicken Haven</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
</head>


<body>


<?php require_once "../bars.php"; ?>
    <div class="main-container">
        <div class="form-container">
        <h2>Marché</h2>
        <p>Vous avez actuellement <strong><?php echo $eggs; ?></strong> œufs.</p>
        
        <div class="egg-shop">
        <?php foreach ($openable_eggs as $egg): ?>
            <div class="egg-item">
                <img class="egg-display" src="/resources/images/eggs/<?php echo htmlspecialchars($egg['image_url']); ?>.png" alt="<?php echo htmlspecialchars($egg['name']); ?>">
                <p><?php echo htmlspecialchars($egg['name']); ?></p>
                <a href="#" class="info-button">
                    <img src="/resources/images/more.png" alt="Infos">
                </a>
                <button onclick="buyEgg(<?php echo $egg['id']; ?>, <?php echo $egg['price']; ?>)"><?php echo $egg['price']; ?> œufs</button>
            </div>
        <?php endforeach; ?>
    </div>
    
    </div>
</div>
</body>

<script>
</script>
