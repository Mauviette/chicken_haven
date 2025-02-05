<?php
session_start();
require_once '../../database/db_connect.php'; // Connexion à la BDD

$user_id = $_SESSION['user_id'];

// Récupérer les poules possédées par l'utilisateur
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.image_url, uc.count, c.rarity, c.effect
    FROM chickens c
    JOIN user_chickens uc ON c.id = uc.chicken_id
    WHERE uc.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$ownedChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les poules manquantes
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.image_url, c.rarity, c.effect
    FROM chickens c
    WHERE c.id NOT IN (SELECT chicken_id FROM user_chickens WHERE user_id = :user_id)
");
$stmt->execute(['user_id' => $user_id]);
$missingChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);


function updateIncubator($user_id, $chicken_id, $slot_number) {
    global $pdo;
    // Vérifier si le slot est déjà occupé
    $stmt = $pdo->prepare("SELECT * FROM incubators WHERE user_id = :user_id AND slot_number = :slot_number");
    $stmt->execute(['user_id' => $user_id, 'slot_number' => $slot_number]);
    $existingSlot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingSlot) {
        // Mettre à jour le slot existant
        $stmt = $pdo->prepare("UPDATE incubators SET chicken_id = :chicken_id WHERE user_id = :user_id AND slot_number = :slot_number");
        $stmt->execute(['chicken_id' => $chicken_id, 'user_id' => $user_id, 'slot_number' => $slot_number]);
    } else {
        // Insérer un nouveau slot
        $stmt = $pdo->prepare("INSERT INTO incubators (user_id, chicken_id, slot_number) VALUES (:user_id, :chicken_id, :slot_number)");
        $stmt->execute(['user_id' => $user_id, 'chicken_id' => $chicken_id, 'slot_number' => $slot_number]);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Poules - Chicken Haven</title>
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
    <link rel="stylesheet" href="../main/style.css">
</head>
<body>

    <?php require_once "../bars.php"; ?>

    
    <div class="main-container" style="margin-top: 25%; min-width: 40%;">
        <div class="form-container">
            <h1>🪹Couvoir</h1>
            <div class="nest-container">
                <?php
                // Récupérer les poules dans les incubateurs
                $stmt = $pdo->prepare("
                SELECT i.slot_number, c.name, c.image_url, c.rarity, c.effect, c.id
                FROM incubators i
                JOIN chickens c ON i.chicken_id = c.id
                WHERE i.user_id = :user_id
                ORDER BY i.slot_number
                ");
                $stmt->execute(['user_id' => $user_id]);
                $incubatorChickens = $stmt->fetchAll(PDO::FETCH_ASSOC);

                for ($slot = 1; $slot <= 3; $slot++):
                    $chicken = array_filter($incubatorChickens, function($incubatorChicken) use ($slot) {
                        return $incubatorChicken['slot_number'] == $slot;
                    });
                    $chicken = reset($chicken);
                    ?>
                    <div class="nest-slot" style="position: relative;">
                        <img src="/resources/images/chicken_nest.png" alt="Nid <?php echo $slot; ?>" class="nest-image">
                            <?php if ($chicken): ?>
                                <img src="/resources/images/chickens/<?php echo htmlspecialchars($chicken['image_url']); ?>.png" alt="<?php echo htmlspecialchars($chicken['name']); ?>" class="chicken-on-nest" style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); width: 10   0%; height: 100%; cursor: pointer;" onclick="showChickenDetails('<?php echo htmlspecialchars($chicken['name']); ?>', '/resources/images/chickens/<?php echo htmlspecialchars($chicken['image_url']); ?>.png', '<?php echo htmlspecialchars($chicken['rarity']); ?>', '<?php echo htmlspecialchars($chicken['id']); ?>')">
                            <?php endif; ?>
                    </div>
                
                    <button id="ajouterPouleBtn" style="display: none;">Ajouter Poule</button>
                <?php endfor; ?>
            </div>
            

            <h2>🐔 Poules possédées</h2>
            <div class="chicken-container friends-section">
                <?php if (empty($ownedChickens)): ?>
                    <p>Aucune poule obtenue pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($ownedChickens as $chicken): ?>
                        <div class="chicken-card <?php echo htmlspecialchars($chicken['rarity']); ?>">
                            <div class="button-container">
                                <a href="#" class="plus-button">
                                    <img src="/resources/images/plus.png" alt="Plus">
                                </a>
                                <span class="tooltip">
                                    <a href="#" class="more-button">
                                        <img src="/resources/images/more.png" alt="More">
                                        <span class="tooltip-text"><?php echo htmlspecialchars($chicken['effect']); ?></span>
                                    </a>
                                </span>
                            </div>

                            <img src="/resources/images/chickens/<?php echo htmlspecialchars($chicken['image_url']); ?>.png" alt="<?php echo htmlspecialchars($chicken['name']); ?>">
                            <p><?php echo htmlspecialchars($chicken['name']); ?> (x<?php echo $chicken['count']; ?>)</p>
                            <span class="chicken-effect" style="display: none;"><?php echo htmlspecialchars($chicken['effect']); ?></span>
                            <span class="chicken-id" style="display: none;"><?php echo htmlspecialchars($chicken['id']); ?></span>
                            <span class="rarity-label <?php echo htmlspecialchars(strtolower($chicken['rarity'])); ?>"><?php echo ucfirst($chicken['rarity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h2>❌ Poules manquantes</h2>
            <div class="chicken-container friends-section">
                <?php if (empty($missingChickens)): ?>
                    <p>Bravo ! Tu as collecté toutes les poules ! 🎉</p>
                <?php else: ?>
                    <?php foreach ($missingChickens as $chicken): ?>
                        <div class="chicken-card missing <?php echo htmlspecialchars($chicken['rarity']); ?>">
                            <img src="/resources/images/chickens/mystery.png" alt="Poule mystère">
                            <p>???</p>
                            <span class="rarity-label"><?php echo ucfirst($chicken['rarity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    
    <div id="chickenPopup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" id="closeChickenPopup">&times;</span>
            <h2>Détails de la Poule</h2>
            <div id="chickenDetails"></div>
        </div>
    </div>
    
    <div id="overlay"></div>

    <style>
        .chicken-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .chicken-card {
            border: 2px solid green;
            padding: 3%;
            text-align: center;
            position: relative;
            width: 20%;
            height: 20%;
            font-size: 100%
        }
        .chicken-card img {
            width: 90%;
            height: 90%;
        }
        .rarity-label {
            display: block;
            margin-top: 5px;
            font-size: 0.8em;
        }
        
        .common {
            border-color: #ccc;
        }
        .rare {
            border-color: blue;
        }
        .epic {
            border-color: purple;
        }
        .legendary {
            border-color: orange;
        }
        .missing {
            border: 2px solid red;
            opacity: 0.5;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            z-index: 1;
        }
        .button-container img {
            width: 15px;
            height: 15px;
        }
        .nest-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .nest-container img {
            width: 100%;
            height: 100%;
        }
        
        p.common {
            color: #ccc;
        }
        p.rare {
            color: blue;
        }
        p.epic {
            color: purple;
        }
        p.legendary {
            color: orange;
        }

        .tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
        }

        .tooltip .tooltip-text {
        visibility: hidden;
        width: 120px;
        background-color: black;
        color: #fff;
        text-align: center;
        padding: 5px;
        border-radius: 5px;

        /* Positionnement */
        position: absolute;
        bottom: 150%;
        left: 50%;
        transform: translateX(-50%);
        
        /* Animation */
        opacity: 0;
        transition: opacity 0.3s;
        }

        .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        }

        .rarity-label.common {
            color: #ccc;
        }

        .rarity-label.rare {
            color: blue;
        }

        .rarity-label.epic {
            color: purple;
        }

        .rarity-label.legendary {
            color: orange;
        }

        .chicken-card.common {
            background-color: #f0f0f0;
        }

        .chicken-card.rare {
            background-color: #d0e7ff;
        }

        .chicken-card.epic {
            background-color: #f3e5ff;
        }

        .chicken-card.legendary {
            background-color: #ffe5b4;
        }
    </style>

</body>
</html>


<script>

    document.querySelectorAll('.more-button').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const chickenCard = this.closest('.chicken-card');
            const chickenName = chickenCard.querySelector('p').textContent;
            const chickenImage = chickenCard.querySelector('img:not(.plus-button img, .more-button img)').src;
            const chickenRarity = chickenCard.querySelector('.rarity-label').textContent;
            const chickenId = chickenCard.querySelector('.chicken-id').textContent;

            showChickenDetails(chickenName, chickenImage, chickenRarity, chickenId);
        });
    });

    document.getElementById('closeChickenPopup').addEventListener('click', function() {
        document.getElementById('chickenPopup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    });

    document.getElementById('overlay').addEventListener('click', function() {
        document.getElementById('chickenPopup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('chickenPopup')) {
            document.getElementById('chickenPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    });

    function showChickenDetails(name, imageUrl, rarity, id) {
        
        const card = Array.from(document.querySelectorAll('.chicken-card')).find(card => card.querySelector('.chicken-id').textContent === id);
        const effect = card.querySelector('.chicken-effect').textContent;

        document.getElementById('chickenDetails').innerHTML = `
            <img src="${imageUrl}" alt="${name}" style="width: 100px; height: 100px;">
            <p>${name}</p>
            <p class="${rarity.toLowerCase()}">${rarity.charAt(0).toUpperCase() + rarity.slice(1).toLowerCase()}</p>

            <p>${effect.replace(/\n/g, '<br>')}</p>
        `;

        document.getElementById('chickenPopup').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    document.querySelectorAll('.plus-button').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();


        const chickenCard = this.closest('.chicken-card');
        const chickenName = chickenCard.querySelector('p').textContent;
        const chickenImage = chickenCard.querySelector('img:not(.plus-button img, .more-button img)').src;
        const chickenRarity = chickenCard.querySelector('.rarity-label').textContent;
        const chickenEffect = chickenCard.querySelector('.chicken-effect').textContent;
        const chickenId = chickenCard.querySelector('.chicken-id').textContent;

        const userId = <?php echo json_encode($user_id); ?>;

        addChickenToIncubator(userId, chickenId, chickenName, chickenImage, chickenRarity, chickenEffect);
        });
    });

    function addChickenToIncubator(userId, chickenId, chickenName, chickenImage, chickenRarity, chickenEffect) {
        const slot = prompt('Dans quel slot voulez-vous ajouter la poule ? (1, 2 ou 3)');

        if (!['1', '2', '3'].includes(slot)) {
            alert('Veuillez entrer un numéro de slot valide.');
            return;
        }

        console.log('Ajouter poule sur l\'emplacement n°' + slot);

        // Envoyer la requête AJAX pour enregistrer la poule dans la base de données
        fetch('add_chicken.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                user_id: userId,
                slot_number: slot,
                chicken_id: chickenId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                //Ajouter la poule visuellement
                ajoutPoule(userId, slot, chickenId, chickenName, chickenImage, chickenRarity, chickenEffect);
            } else {
                alert("Erreur : " + data.message);
            }
        })
        .catch(error => console.error("Erreur : ", error));
    }

    function ajoutPoule (userId, slot, chickenId, chickenName, chickenImage, chickenRarity) {
        // Retirer la poule si elle est déjà dans un autre emplacement
            document.querySelectorAll('.nest-slot img').forEach(img => {
            if (img.src === chickenImage) {
                img.parentNode.removeChild(img);
            }
        });

        // Retirer la poule qui était dans le slot
        const nestSlot = document.querySelectorAll('.nest-slot')[slot - 1];
        if (nestSlot) {
            const existingChicken = nestSlot.querySelector('img.chicken-on-nest');
            if (existingChicken) {
                existingChicken.remove();
            }
        }

        // Ajouter la poule sur le nid correspondant sur la sidebar de droite (juste besoin de l'image et de l'alt), retirer la poule si elle est déjà dans un autre emplacement et retirer la poule qui était dans le slot (modifier ou créer "chicken-on-nest-side", dans le nid "nest-slot-side", le tout est dans "incubator-container-side")
        // Retirer la poule si elle est déjà dans un autre emplacement sur la sidebar de droite
        document.querySelectorAll('.chicken-on-nest-side').forEach(img => {
            if (img.src === chickenImage) {
                img.parentNode.removeChild(img);
            }
        });

        // Retirer la poule qui était dans le slot sur la sidebar de droite
        const nestSlotSide = document.querySelectorAll('.nest-slot-side')[slot - 1];
        if (nestSlotSide) {
            const existingChickenSide = nestSlotSide.querySelector('img.chicken-on-nest-side');
            if (existingChickenSide) {
                existingChickenSide.remove();
            }
        }

        // Ajouter la nouvelle poule sur la sidebar de droite
        const chickenSide = document.createElement('img');
        chickenSide.src = chickenImage;
        chickenSide.alt = chickenName;
        chickenSide.classList.add('chicken-on-nest-side');
        chickenSide.style.position = 'absolute';
        chickenSide.style.top = '0';
        chickenSide.style.left = '0';

        nestSlotSide.appendChild(chickenSide);


        // Ajouter la nouvelle poule
        const chicken = document.createElement('img');
        chicken.src = chickenImage;
        chicken.alt = chickenName;
        chicken.classList.add('chicken-on-nest');
        chicken.style.position = 'absolute';
        chicken.style.top = '-20px';
        chicken.style.left = '50%';
        chicken.style.transform = 'translateX(-50%)';
        chicken.style.width = '100%';
        chicken.style.height = '100%';
        chicken.style.cursor = 'pointer';
        chicken.onclick = function() {
            showChickenDetails(chickenName, chickenImage, chickenRarity, chickenId);
        };

        nestSlot.appendChild(chicken);
    }
</script>


