<?php
require_once '../../scripts/check_eggs_prices.php';

session_start();
require_once '../../database/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "Veuillez vous connecter.";
    exit;
}

// Récupérer le nombre d'œufs du joueur
$stmt = $pdo->prepare('SELECT eggs FROM scores WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$eggs = $stmt->fetchColumn();


// Récupérer les œufs ouvrables
$stmt = $pdo->prepare('SELECT id, name, image_url, price, buyable, probability_common, probability_rare, probability_epic, probability_legendary FROM openable_eggs WHERE limited = 0');
$stmt->execute();
$openable_eggs = $stmt->fetchAll();

// Récupérer les contenus des oeufs ouvrables (egg_id, chicken_id, rarity)
$stmt = $pdo->prepare('SELECT * FROM egg_contents');
$stmt->execute();
$openable_eggs_content = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marché - Chicken Haven</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="icon" href="/resources/images/game.png" type="image/x-icon">
</head>


<body>


<?php require_once "../bars.php"; ?>
    <div class="main-container">
        <div class="form-container">
        <h2>Marché</h2>
        <p id="eggs-indicator">Vous avez actuellement <strong><?php echo $eggs; ?></strong> œufs.</p>
        
        <div class="egg-shop">
        <?php foreach ($openable_eggs as $egg): ?>
            <div class="egg-item" egg-id="<?php echo $egg['id']; ?>">
                <img class="egg-display" src="/resources/images/eggs/<?php echo htmlspecialchars($egg['image_url']); ?>.png" alt="<?php echo htmlspecialchars($egg['name']); ?>">
                <p><?php echo htmlspecialchars($egg['name']); ?></p>
                <a href="#" class="info-button">
                    <img src="/resources/images/more.png" alt="Infos">
                </a>
                <button class="buy-egg"><?php echo $egg['price']; ?> œufs</button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="chickenRewardList" class="popup">
        <div class="popup-content">
            <div class="popup-header">
                <h2>Récompenses possibles</h2>
                <span class="close" onclick="closePopup()">&times;</span>
            </div>

            <div class="chicken-list"></div>
        </div>
     </div>
    
    <div id="overlay"></div>

    </div>
</div>
</body>

<script>
    document.getElementById('overlay').addEventListener('click', closePopup);

    document.querySelectorAll('.info-button').forEach(button => {
        button.addEventListener('click', function(event) {
            
            const eggId = this.closest('.egg-item').getAttribute('egg-id');

            fetch('/scripts/get_avalaible_chickens.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ egg_id: eggId })
            })
            .then(response => response.json())
            .then(data => {
                data.chickens = Array.isArray(data.chickens) ? data.chickens : []; //Conversion en tableau
                const popupBody = document.querySelector('.chicken-list');
                document.querySelector('#chickenRewardList .popup-header h2').textContent = 'Récompenses possibles';
                popupBody.innerHTML = '';
                if (data.success && Array.isArray(data.chickens)) {
                    data.chickens.forEach(chicken => {
                        const chickenElement = document.createElement('div');
                        chickenElement.classList.add('chicken-item');
                        chickenElement.classList.add(chicken.rarity);
                        rarityString = rarityToString(chicken.rarity);
                        chickenElement.innerHTML = `
                            <img src="/resources/images/chickens/${chicken.image_url}" alt="${chicken.name}">
                            <p>${chicken.name}</p>
                            <p>${chicken.probability}%</p>
                            <b><p class="${chicken.rarity}">${rarityString}</p></b>
                        `;
                        popupBody.appendChild(chickenElement);
                    });
                } else {
                    popupBody.innerHTML = '<p>Aucune récompense disponible.</p>';
                }
            })
            .catch(error => console.error('Error:', error));

            event.preventDefault();
            const eggItem = this.closest('.egg-item');
            const eggName = eggItem.querySelector('p').textContent;
            const popupBody = document.querySelector('.chicken-list');
            document.getElementById('chickenRewardList').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        });
    });

    function closePopup() {
        document.getElementById('chickenRewardList').style.display = 'none';
        document.getElementById('chickenRewardList').style.height = 'auto';
        document.getElementById('chickenRewardList').style.backgroundColor = 'rgba(255, 255, 255, 1)';
        document.getElementById('overlay').style.display = 'none';

    }

    document.querySelectorAll('.buy-egg').forEach(button => {
        button.addEventListener('click', function(event) {
            const eggId = this.closest('.egg-item').getAttribute('egg-id');
            fetch('/scripts/add_random_chicken_from_egg.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ egg_id: eggId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Poule trouvée : ' + data.chicken.name + ' (' + data.chicken.rarity + ')' + '. ID : ' + data.chicken.id);

                    const popupBody = document.querySelector('.chicken-list');
                    popupBody.style = "justify-content: center; display: flex;";
                    popupBody.innerHTML = `
                        <div class="egg-animation">
                            <img src="/resources/images/eggs/${data.egg_image_url}.png" alt="Egg" class="egg-shake">
                        </div>
                    `;
                    document.querySelector('#chickenRewardList .popup-header h2').textContent = '';

                    document.getElementById('chickenRewardList').style.display = 'block';
                    document.getElementById('chickenRewardList').style.height = '30%';
                    document.getElementById('chickenRewardList').style.backgroundColor = 'transparent';
                    document.getElementById('overlay').style.display = 'block';
                    document.getElementById('chickenRewardList').style.width = '50%';
                    
                    
                    const scoreElements = document.querySelectorAll('.score');
                    scoreElements.forEach(element => {
                        element.textContent = new Intl.NumberFormat().format(data.newScore) + ' œufs';
                    });

                    const eggsIndicator = document.getElementById('eggs-indicator');
                    eggsIndicator.innerHTML = `Vous avez actuellement <strong>${data.newScore}</strong> œufs.`;

                    setTimeout(() => {
                        const eggAnimation = document.querySelector('.egg-animation');

                        
                        // Créer plusieurs fragments d'œuf
                        for (let i = 0; i < 18; i++) {
                            const fragment = document.createElement('div');
                            fragment.classList.add('egg-fragment-open');
                            //fragment.style.backgroundImage = `url('/resources/images/eggs/${data.egg_image_url}_fragment.png')`;
                            fragment.style.backgroundImage = `url('/resources/images/feather.png')`;


                            // Générer une direction aléatoire pour le fragment
                            const randomX = Math.random() * 2 - 1; // Valeur aléatoire entre -1 et 1
                            const randomRotation = Math.floor(Math.random() * 360) + 'deg'; // Rotation initiale aléatoire

                            // Appliquer les variables CSS personnalisées
                            fragment.style.setProperty('--random-x', randomX);
                            fragment.style.setProperty('--start-rotation', randomRotation);

                            // Positionner le fragment à l'endroit du clic
                            const randomOffsetX = (Math.random() - 0.5) * 250;
                            const randomOffsetY = (Math.random() - 0.5) * 250;
                            fragment.style.left = `calc(50% + ${randomOffsetX}px)`;
                            fragment.style.top = `calc(50% + ${randomOffsetY}px)`;

                            //Déterminer une taille aléatoire
                            const randomSize = Math.floor(Math.random() * 30) + 30; // Valeur aléatoire entre 50 et 100
                            fragment.style.width = `${randomSize}px`;
                            fragment.style.height = `${randomSize}px`;

                            // Ajouter le fragment au body
                            document.body.appendChild(fragment);

                            // Supprimer le fragment après la fin de l'animation
                            fragment.addEventListener('animationend', () => {
                                fragment.remove();
                            });
                        }
                        
                        document.getElementById('chickenRewardList').style.backgroundColor = 'rgba(255, 255, 255, 1)';
                        document.getElementById('chickenRewardList').style.height = 'auto';
                        document.getElementById('chickenRewardList').style.width = '20%';

                        rarityString = rarityToString(data.chicken.rarity);
                        document.querySelector('#chickenRewardList .popup-header h2').textContent = 'Poule obtenue';
                            popupBody.innerHTML = `
                                <div class="chicken-item ${data.chicken.rarity}">
                                    <img src="/resources/images/chickens/${data.chicken.image_url}.png" alt="${data.chicken.name}">
                                    <p>${data.chicken.name}</p>
                                    <b><p class="${data.chicken.rarity}">${rarityString}</p></b>
                                </div>
                            `;


                        // Rendre le chickenRewardList invisible
                        document.getElementById('chickenRewardList').style.opacity = '0';

                        // Attendre un court instant avant de le rendre visible
                        setTimeout(() => {
                            document.getElementById('chickenRewardList').style.opacity = '1';
                            document.getElementById('chickenRewardList').style.transition = 'opacity 0.5s ease-in-out';
                        }, 50);
                    }, 2000); // Adjust the timing as needed

                    
                        
                } else {
                    alert(data.error + "\nDétails : " + data.details);
                }
            })
            .catch(error => console.error('Error:', error));
            event.preventDefault();
        });
    });

function rarityToString(rarity) {
    switch (rarity) {
        case "common" : return "Commun";
        case "rare" : return "Rare";
        case "epic" : return "Épique";
        case "legendary" : return "Légendaire";
    }
}

</script>

<style>
    .egg-animation {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        width: 200px;
        margin: 0 auto;
    }

    .egg-shake {
        animation: shake 0.8s;
        animation-iteration-count: infinite;
    }

    @keyframes shake {
        0% { transform: translate(1px, 1px) rotate(0deg); }
        10% { transform: translate(-1px, -2px) rotate(-1deg); }
        20% { transform: translate(-3px, 0px) rotate(1deg); }
        30% { transform: translate(3px, 2px) rotate(0deg); }
        40% { transform: translate(1px, -1px) rotate(1deg); }
        50% { transform: translate(-1px, 2px) rotate(-1deg); }
        60% { transform: translate(-3px, 1px) rotate(0deg); }
        70% { transform: translate(3px, 1px) rotate(-1deg); }
        80% { transform: translate(-1px, -1px) rotate(1deg); }
        90% { transform: translate(1px, 2px) rotate(0deg); }
        100% { transform: translate(1px, -2px) rotate(-1deg); }
    }
</style>