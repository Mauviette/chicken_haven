<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /index.php');
    exit();
}

require_once '../profile_picture.php'; // Connexion à la base de données

$username = $_SESSION['username'];
$displayname = $_SESSION['displayname'];

// Récupérer le score actuel de l'utilisateur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$eggs = $stmt->fetchColumn();

// Récupérer les images disponibles dans la base de données
$stmt = $pdo->prepare('SELECT c.id, c.name, c.image_url, c.rarity 
                       FROM chickens c
                       JOIN user_chickens uc ON c.id = uc.chicken_id
                       WHERE uc.user_id = :user_id
                       UNION
                       SELECT c.id, c.name, c.image_url, c.rarity 
                       FROM chickens c
                       WHERE c.id = 1');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$profileIcons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est un tricheur
$stmt = $pdo->prepare('SELECT cheater FROM users WHERE id = :user_id');
$stmt->execute(['user_id'=> $_SESSION['user_id']]);
$cheater = $stmt->fetchColumn();

//Obtenir le nombre de poules sur le jeu
$stmt = $pdo->prepare('SELECT COUNT(*) FROM chickens');
$stmt->execute();
$total_chickens = $stmt->fetchColumn();

//Obtenir le nombre de poules de l'utilisateur (de la table user_chickens, vérifier par rapport à user_id)
$stmt = $pdo->prepare('SELECT COUNT(*) FROM user_chickens WHERE user_id = :user_id');
$stmt->execute(['user_id'=> $_SESSION['user_id']]);
$chickens_obtained = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Chicken Haven</title>    
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    <div class="main-container">
        <div class="form-container">
            <h1>Mon profil</h1>

            <div id="return_message"></div>
            <p><strong id="username"><?php echo htmlspecialchars($displayname); ?></strong> (<strong>@<?php echo htmlspecialchars($username); ?></strong>)</p>

            <button id="editDisplayNameBtn" style="margin: 2%;">Modifier le nom d'affichage</button>

            <!-- Image actuelle du profil -->
            <img id="currentProfilePic" src="<?php echo getProfilePicture($_SESSION['user_id']); ?>" alt="Profil" class="profile-icon-big" style="cursor: pointer;">
            <p>Cliquez sur l'image pour changer de photo de profil.</p>

            <!-- Popup pour les icônes de profil -->
            <div id="profileIconsPopup" class="popup">
                <div class="popup-content">
                    <span class="close" id="closeProfileIconsPopup">&times;</span>
                    <h2>Choisir une nouvelle photo de profil</h2>
                    <div class="profile-icons-container">
                        <?php foreach ($profileIcons as $icon): ?>
                            <img src="<?php echo htmlspecialchars("/resources/images/chickens/" . $icon['image_url']) . ".png"?>" 
                                alt="<?php echo htmlspecialchars($icon['name']); ?>" 
                                class="profile-icon-option" 
                                data-icon-id="<?php echo $icon['id']; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>


            <div id="editDisplayNamePopup" class="popup">
                <div class="popup-content">
                    <span class="close" id="closeEditNamePopup">&times;</span>
                    <h2>Modifier le nom d'affichage</h2>
                    <form id="editDisplayNameForm">
                        <label for="newDisplayName">Nouveau nom d'affichage:</label>
                        <input type="text" id="newDisplayName" name="newDisplayName" required>
                        <button type="submit">Enregistrer</button> 
                    </form>
                </div>
            </div>

            <div id="overlay"></div>

            <?php if ($cheater) { echo '<p style="color: red;"><b>Tricheur</b></p>'; }?>
            <p>Nombre d'œufs : <strong><?php echo number_format($eggs); ?></strong></p>
            <p>Poules débloquées : <strong><?php echo $chickens_obtained; ?>/<?php echo $total_chickens; ?></strong></p>

            <br>
            <a href="/scripts/logout.php">Se déconnecter</a><br><br>

            
        </div>
    </div>

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

        // Afficher la modale lorsqu'on clique sur l'image actuelle
        document.getElementById('currentProfilePic').addEventListener('click', function() {
            document.getElementById('profileIconsPopup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        });

        // Fermer la modale
        document.getElementById('closeProfileIconsPopup').addEventListener('click', function() {
            document.getElementById('profileIconsPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        });

        // Fermer la modale du nom d'affichage
        document.getElementById('closeEditNamePopup').addEventListener('click', function() {
            document.getElementById('editDisplayNamePopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        });

        // Fermer la modale en cliquant sur l'overlay
        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('profileIconsPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        });

        // Gérer le choix de la nouvelle image de profil
        document.querySelectorAll('.profile-icon-option').forEach(function(icon) {
            icon.addEventListener('click', function() {
                const iconId = this.dataset.iconId;

                // Envoyer l'ID de l'image au serveur via une requête AJAX
                fetch('update_profile_picture.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'icon_id=' + iconId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour l'image actuelle
                        document.getElementById('currentProfilePic').src = this.src;
                        document.getElementById("profile-icon").src = this.src;
                        // Fermer la modale
                        document.getElementById('profileIconsPopup').style.display = 'none';
                        document.getElementById('overlay').style.display = 'none';
                        document.getElementById('return_message').innerHTML = data.message;
                        document.getElementById('return_message').style.color = 'green';

                    } else {
                        document.getElementById('profileIconsPopup').style.display = 'none';
                        document.getElementById('overlay').style.display = 'none';
                        document.getElementById('return_message').innerHTML = data.message;
                        document.getElementById('return_message').style.color = 'red';
                    }
                });
            });
        });

        document.getElementById('editDisplayNameForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('update_displayname.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    //Mettre à jour les pseudos sur l'écran
                    document.querySelector('.username').textContent = formData.get('newDisplayName');
                    document.getElementById('username').textContent = formData.get('newDisplayName');

                    document.getElementById('editDisplayNamePopup').style.display = 'none';
                    document.getElementById('overlay').style.display = 'none';
                    document.getElementById('return_message').innerHTML = data.message;
                    document.getElementById('return_message').style.color = 'green';
                } else {
                    document.getElementById('return_message').innerHTML = data.message;
                    document.getElementById('return_message').style.color = 'red';
                    document.getElementById('editDisplayNamePopup').style.display = 'none';
                    document.getElementById('overlay').style.display = 'none';
                }
            });
        });





    </script>
</body>
</html>
