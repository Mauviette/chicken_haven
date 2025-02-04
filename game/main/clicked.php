<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'];
$base_increment = 1;

//Mettre $bonus à eggs_after_decimal
$stmt = $pdo->prepare('SELECT eggs_after_decimal FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$bonus = $stmt->fetchColumn();

//Récupérer les 3 emplacements de couveuses
$stmt = $pdo->prepare('SELECT * FROM incubators WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$incubators = $stmt->fetchAll();

// Vérifier si une poule rousse (id = 1) est en couveuse
$hasChickenInIncubator = false;
foreach ($incubators as $incubator) {
    if ($incubator['chicken_id'] === 1) {
        $hasChickenInIncubator = true;
        break;
    }
}

// Si la poule rousse est en couveuse, récupérer le nombre de poules rousses possédées
if ($hasChickenInIncubator) {
    $stmt = $pdo->prepare('
        SELECT count 
        FROM user_chickens 
        WHERE user_id = :user_id 
        AND chicken_id = (SELECT id FROM chickens WHERE name = "Poule Rousse")
    ');
    $stmt->execute(['user_id' => $user_id]);
    $chickenCount = $stmt->fetchColumn() ?: 0; // Si aucune poule rousse, on met 0

    // Appliquer l'effet de la poule rousse
    $bonus += $chickenCount * 0.5; // +1 œuf par clic par poule rousse
}
// Dans bonus, retirer tout ce qui est après le point et le mettre dans $bonus_after_decimal
$bonus_after_decimal = $bonus - floor($bonus);
$bonus = floor($bonus);

// Calcul du total des œufs gagnés
$increment = $base_increment + $bonus;

// Mettre à jour les œufs dans la base
$stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_last_day = eggs_last_day + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_earned_total = eggs_earned_total + :increment WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'user_id' => $user_id]);

$stmt = $pdo->prepare('UPDATE scores SET eggs_after_decimal = :bonus_after_decimal WHERE user_id = :user_id');
$stmt->execute(['bonus_after_decimal' => $bonus_after_decimal, 'user_id' => $user_id]);

// Récupérer le nouveau score
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$newScore = $stmt->fetchColumn();

echo json_encode(['success' => true, 'newScore' => $newScore, 'increment' => $increment]);
?>
