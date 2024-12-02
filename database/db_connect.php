<?php
// Paramètres de connexion à la base de données
$host = 'localhost'; // Adresse du serveur (souvent localhost)
$dbname = 'chicken_haven_db'; // Nom de la base de données
$user = 'root'; // Nom d'utilisateur de la base de données (par défaut 'root' pour WAMP)
$pass = ''; // Mot de passe de la base de données (par défaut vide pour WAMP)

try {
    // Création d'une instance PDO pour établir la connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Configuration des options PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Affiche les erreurs en mode exception
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Définit le mode de récupération par défaut des résultats sous forme de tableau associatif
} catch (PDOException $e) {
    // Gère les erreurs de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
