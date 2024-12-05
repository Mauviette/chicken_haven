<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /chicken_haven/index');
    exit();
}

require_once '../profile_picture.php'; // Connexion à la base de données

$username = $_SESSION['username'];
$displayname = $_SESSION['displayname'];

// Récupérer le score actuel de l'utilisateur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$eggs = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Chicken Haven</title>    
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/game.png" type="image/x-icon">
</head>
<body>

    <?php  require_once "../bars.php"; ?>

    <div class="form-container">
        
        <?php if (isset($_GET['error'])): ?>
                <p class="error" style = "color: red; font-weight: bold;">
            <?php
                if ($_GET['error'] == 1) {
                    echo "Le nom d'affichage ne peut pas être vide.";
                } elseif ($_GET['error'] == 2) {
                    echo "Le nom d'affichage doit faire entre 2 et 30 caractères.";
                }
            ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
                <p class="success" style = "color: green; font-weight: bold;">
            <?php
                if ($_GET['success'] == 1) {
                    echo "Nom d'affichage modifié avec succès.";
                }
            ?>
            </p>
        <?php endif; ?>

        <h1>Mon profil</h1>
        <p><strong><?php echo htmlspecialchars($displayname); ?></strong> (<strong>@<?php echo htmlspecialchars($username); ?></strong>)</p>

        <button id="editDisplayNameBtn">Modifier le nom d'affichage</button>
        <br><br><br> 

        <div id="editDisplayNamePopup" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Modifier le nom d'affichage</h2>
                <form id="editDisplayNameForm" action="update_displayname" method="post">
                    <label for="newDisplayName">Nouveau nom d'affichage:</label>
                    <input type="text" id="newDisplayName" name="newDisplayName" required>
                    <button type="submit">Enregistrer</button> 
                </form>
            </div>
        </div>

        <div id="overlay"></div>




        <script>
        document.getElementById('editDisplayNameBtn').addEventListener('click', function() {
            document.getElementById('editDisplayNamePopup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        });

        document.querySelector('.popup .close').addEventListener('click', function() {
            document.getElementById('editDisplayNamePopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('overlay')) {
                document.getElementById('editDisplayNamePopup').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
            }
        });
        </script>

        
        <style>
        .popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #888;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .popup .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999;
        }
        </style>


        <img src="<?php echo getProfilePicture($_SESSION['user_id']);?>" alt="Profil" class="profile-icon-big">
        <br>
        
        <p>Nombre d'œufs : <strong><?php echo number_format($eggs); ?></strong></p>

        <br>
        <a href="/chicken_haven/scripts/logout">Se déconnecter</a><br><br>
    
    </div>
</body>
</html>
