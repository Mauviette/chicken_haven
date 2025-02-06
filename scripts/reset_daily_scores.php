<?php
require_once '../../database/db_connect.php';

$stmt = $pdo->prepare('UPDATE scores SET eggs_last_day = 0');
$stmt->execute();

echo "Daily scores reset successfully.";
?>
