<?php
session_start();
require_once '../database/db_connect.php'; // Connexion à la base de données

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));
    $displayname = trim($_POST['displayname']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    $errors = [];

    // Vérifie que tous les champs sont remplis
    if (empty($username) || empty($displayname) || empty($password) || empty($password_confirm)) {
        $errors[] = "Tous les champs sont requis.";
    }

    // Vérifie que l'username ne contient que des lettres, chiffres, _
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Caractères interdits dans l'identifiant.";
    }

    // Vérifie que le mot de passe ne contient que des lettres, chiffres, _
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $password)) {
        $errors[] = "Caractères interdits dans le mot de passe.";
    }

    // Vérifie que les mots de passe correspondent
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifie que le nom d'affichage fait de 4 à 20 caractères
    if (strlen($displayname) < 1 || strlen($displayname) > 30) {
        $errors[] = "Le nom d'affichage doit faire entre 1 et 30 caractères.";
    }

    // Vérifie que l'identifiant fait de 4 à 20 caractères
    if (strlen($username) < 4 || strlen($username) > 20) {
        $errors[] = "L'identifiant doit faire entre 4 et 20 caractères.";
    }

    // Vérifie que le mot de passe fait de 4 à 20 caractères
    if (strlen($password) < 4 || strlen($password) > 20) {
        $errors[] = "Le mot de passe doit faire entre 4 et 20 caractères.";
    }

    // Vérifie la disponibilité du username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Cet identifiant est déjà pris.";
    }

    
    // Si aucune erreur, insère l'utilisateur dans la base de données
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, displayname, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $displayname, $hashed_password]);



        // Redirige vers index avec un message de succès
        header("Location: index?register_success=1");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Chicken Haven</title>
    <link rel="stylesheet" href="style.css">    
    <link rel="icon" href="/chicken_haven/resources/images/login.png" type="image/x-icon">
</head>
<body>
    <div class="form-container">
        <h1>Inscription</h1>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p style = "color: red; font-weight: bold;"><?= htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div>
                <label for="username">Nom d'utilisateur :</label><br>
                <input type="text" name="username" id="username" required>
            </div>
            <div>
                <label for="displayname">Nom affiché :</label><br>
                <input type="text" name="displayname" id="displayname" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label><br>
                <input type="password" name="password" id="password" required>
            </div>
            <div>
                <label for="password_confirm">Confirmer le mot de passe :</label><br>
                <input type="password" name="password_confirm" id="password_confirm" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        <p><a href="../index">Retour à la connexion</a></p>
    </div>
</body>
</html>
