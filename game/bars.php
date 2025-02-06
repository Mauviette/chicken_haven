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


//Calculer les poulets par seconde

// Récupérer le nombre de poules noires en couveuse
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM incubators 
    INNER JOIN chickens ON incubators.chicken_id = chickens.id
    WHERE incubators.user_id = :user_id AND chickens.name = 'Poule noire'
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$blackChickenCount = $stmt->fetchColumn();

// Calculer l'augmentation des œufs par seconde
$eggsPerSecond = 0.1 * $blackChickenCount;


// Récupérer le score actuel de l'utilisateur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$currentScore = $stmt->fetchColumn();


echo '<!-- Barre de navigation -->
    <div class="navbar">
    <div class="profile-section">
        <a href="/game/my_profile/index.php" class="profile-link">
        <img src=" ' . getProfilePicture($_SESSION['user_id']) . '" alt="Profil" class="profile-icon" id="profile-icon">
        <span class="username">' . htmlspecialchars($_SESSION['displayname']) . '</span>
        </a>
    </div>

        <div class="patch-notes">
            <button id="loadPatchNotes">📜</button>
        </div>
        
        <!-- Conteneur des patch notes -->
        <div id="patchNotesContainer" class="patch-notes-container">
            <!--button id="closePatchNotes">❌</button-->
            <div id="patchNotes"></div>
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
        <section class="incubator-container-side">
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
        </section>

        <div class="score side-score">
            <span>' . number_format($currentScore, 0, ',', ' ') . ' œufs</span>
        </div>
        
';

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
document.getElementById("patchNotesContainer").style.display = "none";


document.getElementById("loadPatchNotes").addEventListener("click", function () {
    patchNotesContainer = document.getElementById("patchNotesContainer");
    if (patchNotesContainer.style.display === "none") {
        fetch("/scripts/get_patch_notes.php")
            .then(response => response.json())
            .then(data => {
                const patchNotesDiv = document.getElementById("patchNotes");
                const container = document.getElementById("patchNotesContainer");

                patchNotesDiv.innerHTML = "<h3 style='margin-left: 10px; color: black;'>Nouveautés & Mises à jour</h3>"; // Vider avant d'afficher

                if (data.error) {
                    patchNotesDiv.innerHTML = `<p style="color: red;">${data.error}</p>`;
                } else {
                    data.forEach(note => {
                        const noteElement = document.createElement("div");
                        noteElement.classList.add("patch-note");
                        noteElement.innerHTML = note.replace(/\n/g, "<br>");
                        patchNotesDiv.appendChild(noteElement);
                    });
                }

                container.style.display = "block";
            })
            .catch(error => console.error("Erreur :", error));
    }
    else {
        patchNotesContainer.style.display = "none";
    }
});

// Bouton pour fermer le menu
// document.getElementById("closePatchNotes").addEventListener("click", function () {
//    document.getElementById("patchNotesContainer").style.display = "none";
//});
</script>


<script src="/scripts/eggs_per_second.js"></script>
<script src="/scripts/update_session.js"></script>
