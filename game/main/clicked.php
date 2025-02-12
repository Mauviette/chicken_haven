<?php
session_start();
require_once '../../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$base_increment = 1;
$clickCooldown = 10; // Temps minimum entre deux clics (ms)
$maxClicksPerSecond = 19; // Limite des clics par seconde
$now = microtime(true);

// Vérifier si l'utilisateur spamme les clics
if (isset($_SESSION['last_click_time'])) {
    $timeSinceLastClick = ($now - $_SESSION['last_click_time']) * 1000; // Convertir en ms
    
    if ($timeSinceLastClick < $clickCooldown) {
        echo json_encode(['success' => false, 'error' => 'Click too fast']);
        exit();
    }
}
$_SESSION['last_click_time'] = $now;

// Vérifier le nombre de clics par seconde
if (!isset($_SESSION['clicks_per_second'])) {
    $_SESSION['clicks_per_second'] = 0;
    $_SESSION['first_click_time'] = $now;
}
$_SESSION['clicks_per_second']++;

if ($now - $_SESSION['first_click_time'] >= 1) {
    $_SESSION['clicks_per_second'] = 0;
    $_SESSION['first_click_time'] = $now;
}


$stmt = $pdo->prepare('SELECT cheater FROM users WHERE id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$cheating = $stmt->fetchColumn();

// Vérifier si l'utilisateur est un tricheur
if ($_SESSION['clicks_per_second'] > $maxClicksPerSecond && $cheating != 1) {
    $stmt = $pdo->prepare('SELECT last_cheat_time FROM users WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $lastCheatTime = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT NOW()');
    $currentTime = $stmt->fetchColumn();

    //Vériier si l'utilisateur a triché les 5 dernières secondes
    if ($lastCheatTime && (strtotime($currentTime) - strtotime($lastCheatTime)) < 30) {
        echo json_encode(['success' => false, 'error' => 'Cheating detected recently', 'lastCheatTime' => (strtotime($currentTime) - strtotime($lastCheatTime))]);
        exit();
    }

    $stmt = $pdo->prepare('UPDATE users SET last_cheat_time = NOW() WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);

    require_once '../../scripts/alert_cheating.php';


    $stmt = $pdo->prepare('SELECT cheater FROM users WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $cheating = $stmt->fetchColumn();

    if ($cheating == 1) {
        echo json_encode(['success'=> false, 'error' => 'Cheating detected, already alerted']);
    } else {
        echo json_encode(['success'=> false, 'error' => 'Cheating detected']);
    }
    exit();
}



// Récupérer le bonus en fonction des poules en couveuse
$stmt = $pdo->prepare('SELECT eggs_after_decimal FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$bonus = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT * FROM incubators WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$incubators = $stmt->fetchAll();

$hasChickenInIncubator = false;
foreach ($incubators as $incubator) {
    if ($incubator['chicken_id'] === 1) {
        $hasChickenInIncubator = true;
        break;
    }
}

if ($hasChickenInIncubator) {
    $stmt = $pdo->prepare('SELECT count FROM user_chickens WHERE user_id = :user_id AND chicken_id = 1');
    $stmt->execute(['user_id' => $user_id]);
    $chickenCount = $stmt->fetchColumn() ?: 0;
    $bonus += $chickenCount * 0.1;
}

$bonus_after_decimal = $bonus - floor($bonus);
$bonus = floor($bonus);

$increment = $base_increment + $bonus;

$stmt = $pdo->prepare('UPDATE scores SET eggs = eggs + :increment, eggs_last_day = eggs_last_day + :increment, eggs_earned_total = eggs_earned_total + :increment, eggs_after_decimal = :bonus_after_decimal WHERE user_id = :user_id');
$stmt->execute(['increment' => $increment, 'bonus_after_decimal' => $bonus_after_decimal, 'user_id' => $user_id]);

$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$newScore = $stmt->fetchColumn();

echo json_encode(['success' => true, 'newScore' => $newScore, 'increment' => $increment]);
?>
