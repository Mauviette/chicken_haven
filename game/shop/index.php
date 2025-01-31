<?php
session_start();


if (!isset($_SESSION['username']))
{
    header("Location: ../index");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Haven</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="/chicken_haven/resources/images/game.png" type="image/x-icon">
</head>
<body>

  <?php  require_once "../bars.php"; ?>

</body>
</html>