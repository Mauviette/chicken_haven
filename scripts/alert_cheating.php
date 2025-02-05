<?php
require_once '../../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer le nombre d'alertes et le statut de tricheur
$stmt = $pdo->prepare('SELECT nb_cheater_alerts, cheater FROM users WHERE id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


$nb_cheater_alerts = $user['nb_cheater_alerts'] + 1;
$cheater = $user['cheater'];


// Vérifier si l'utilisateur atteint 3 alertes
if ($nb_cheater_alerts >= 3) {
    $cheater = 1;
}

// Mettre à jour la base de données
$stmt = $pdo->prepare('UPDATE users SET nb_cheater_alerts = :nb_cheater_alerts, cheater = :cheater WHERE id = :user_id');
$stmt->execute(['nb_cheater_alerts' => $nb_cheater_alerts, 'cheater' => $cheater, 'user_id' => $user_id]);
?>
