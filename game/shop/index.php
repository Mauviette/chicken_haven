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
        <?php foreach ($eggs as $egg): ?>
            <div class="egg-item">
                <img src="/chicken_haven/resources/images/eggs/<?php echo htmlspecialchars($egg['image_url']); ?>.png" alt="<?php echo htmlspecialchars($egg['name']); ?>">
                <p><?php echo htmlspecialchars($egg['name']); ?></p>
                <p>Prix : <?php echo $egg['price']; ?> œufs</p>
                <button onclick="buyEgg(<?php echo $egg['id']; ?>, <?php echo $egg['price']; ?>)">Acheter</button>
            </div>
        <?php endforeach; ?>
    </div>
    
    </div>
</div>
</body>

<script>
document.getElementById('buySilverEggBtn').addEventListener('click', function () {
    fetch('buy_silver_egg.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: <?php echo json_encode($user_id); ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('marketMessage').innerText = "Vous avez obtenu : " + data.chicken_name;
        } else {
            document.getElementById('marketMessage').innerText = "Erreur : " + data.message;
        }
    })
    .catch(error => console.error('Erreur:', error));
});
</script>
