<?php
session_start();
$file = __DIR__ . '/../session/sessions.json';
$sessions = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$sessions[session_id()] = time();

// Supprimer les sessions inactives (35 secondes d'inactivité)
$sessions = array_filter($sessions, fn($t) => $t > time() - 30);

file_put_contents($file, json_encode($sessions));
?>
