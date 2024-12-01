<?php
session_start();



if (!isset($_SESSION['username']))
{
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Haven</title>
    <link rel="stylesheet" href="../css/game.css">
    <link rel="icon" href="images/game.png" type="image/x-icon">
</head>
<body>

  <?php  require_once "bars.php"; ?>

    <div class="form-container">
        <h1>Bienvenue <?php echo($_SESSION['displayname']);?></h1>
        <a href="../scripts/logout.php" class="logout">Se déconnecter</a>
        <br><br>
    </div>
</body>
</html>


<!-- <script>
  function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.style.left === '-10%') {
      sidebar.style.left = '0';
    } else {
      sidebar.style.left = '-10%';
    }
  }
</script> -->
