<?php
session_start(); // Démarre la session

require_once '../database/db_connect.php'; // Connexion à la base de données

// Vérifie que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Prépare la requête pour récupérer l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Si l'utilisateur existe et le mot de passe est correct
            $_SESSION['username'] = $user['username']; // Stocke le nom d'utilisateur en session
            $_SESSION['displayname'] = $user['displayname']; // Stocke le nom affiché (si nécessaire)
            $_SESSION['user_id'] = $user['id']; // Stocke l'ID de l'utilisateur en session

            //Vérifier si l'utilisateur a un score
            $stmt = $pdo->prepare("SELECT * FROM scores WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $score = $stmt->fetch();

            if (!$score) {
                // Si l'utilisateur n'a pas de score, on lui en crée un
                $stmt = $pdo->prepare("INSERT INTO scores (user_id) VALUES (?)");
                $stmt->execute([$user['id']]);
            }
            
            header("Location: ../game/main/index.php"); // Redirige vers le jeu
            exit();
        } else {
            // Si les identifiants sont incorrects
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        // Si les champs sont vides
        header("Location: index.php?error=2");
        exit();
    }
} else {
    // Si l'accès à ce fichier se fait sans soumettre de formulaire
    header("Location: index.php");
    exit();
}
