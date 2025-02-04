<?php
require_once '../../database/db_connect.php'; // Connexion à la base de données
require_once 'profile_picture.php';

// Vérifier les demandes d'amis si on n'est pas sur la page friends_list
$stmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user2_id = :user_id AND accepted = 0');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$nbPendingRequests = $stmt->fetchColumn();

$notification = ($nbPendingRequests > 0) ? ' <span style="color: gold;">' . $nbPendingRequests . ' </span>' : '';

$sessions = json_decode(file_get_contents(__DIR__ . '/../session/sessions.json'), true);

// Récupérer les poules dans les incubateurs
$stmt = $pdo->prepare("
SELECT i.slot_number, c.name, c.image_url, c.rarity, c.effect, c.id
FROM incubators i
JOIN chickens c ON i.chicken_id = c.id
WHERE i.user_id = :user_id
ORDER BY i.slot_number
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$incubatorChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);



echo '<!-- Barre de navigation -->
    <div class="navbar">
    <div class="profile-section">
        <a href="/game/my_profile/index.php" class="profile-link">
        <img src=" ' . getProfilePicture($_SESSION['user_id']) . '" alt="Profil" class="profile-icon" id="profile-icon">
        <span class="username">' . htmlspecialchars($_SESSION['displayname']) . '</span>
        </a>
    </div>
    <div class="session-section">
        <span>' . count($sessions) . ' en ligne</span>
    </div>
    </div>

    <!-- Barre latérale -->
    <div class="sidebar" style="display: flex; justify-content: center;text-align: center;">
    <ul>
        <li><a href="/game/main/index.php">Accueil</a></li>
        <li><a href="/game/hatchery/index.php">Couvoir</a></li>
        <li><a href="/game/shop/index.php">Marché</a></li>
        <li><a href="/game/social/index.php">Social ' . $notification . '</a></li>
    </ul>
    </div>

    <!-- Barre latérale droite -->
    <div class="sidebar-right">
    <ul>
        <section class="incubator-container-side" style="display: flex; justify-content: center; align-items: center;">
        ';

        for ($slot = 1; $slot <= 3; $slot++) {
            $chicken = array_filter($incubatorChickens, function ($incubatorChicken) use ($slot) {
                return $incubatorChicken['slot_number'] == $slot;
            });
            $chicken = reset($chicken);

            echo '
            <div class="nest-slot-side" style="position: relative; margin: 0 10px;">
                <img src="/resources/images/chicken_nest.png" alt="Nid ' . $slot . '" class="nest-image-side" style="position: absolute; top: 0; left: 0;">';
                
            if ($chicken) {
                echo '
                <img src="/resources/images/chickens/' . htmlspecialchars($chicken['image_url']) . '.png"
                     alt="' . htmlspecialchars($chicken['name']) . '"
                     class="chicken-on-nest-side" style="position: absolute; top: 0; left: 0;">';
            }

            echo '
            </div>';
        }

        echo '
        </section>';

echo '
    </ul>
</div>';


    function updateProfilePicture($newSrc) {
        echo '<script>
            document.getElementById("profile-icon").src = "' . htmlspecialchars($newSrc) . '";
        </script>';
    }

  

?>

<script>
    function updateSession() {
        fetch('/scripts/update_session.php')
            .then(response => response.text())
            .then(data => console.log('Session mise à jour'));
    }

    // Met à jour toutes les 30 secondes
    setInterval(updateSession, 30000);

    // Appel initial dès le chargement de la page
    updateSession();
</script>