<?php
session_start();
require_once '../scripts/db_connect.php'; // Connexion à la base de données

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $displayname = trim($_POST['displayname']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    $errors = [];

    // Vérifie que tous les champs sont remplis
    if (empty($username) || empty($displayname) || empty($password) || empty($password_confirm)) {
        $errors[] = "Tous les champs sont requis.";
    }

    // Vérifie que les mots de passe correspondent
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifie la disponibilité du username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Ce nom d'utilisateur est déjà pris.";
    }

    // Si aucune erreur, insère l'utilisateur dans la base de données
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, displayname, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $displayname, $hashed_password]);

        // Redirige vers index.php avec un message de succès
        header("Location: ../index.php?register_success=1");
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
    <link rel="stylesheet" href="../css/login.css">
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
        <p><a href="../index.php">Retour à la connexion</a></p>
    </div>
</body>
</html>
