(function() {
    function autoIncrementEggs() {
        fetch('/game/auto_add_eggs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const scoreElements = document.querySelectorAll('.score');
                if (scoreElements.length > 0) {
                    scoreElements.forEach(element => {
                        element.textContent = new Intl.NumberFormat().format(data.newScore) + ' œufs';
                    });
                }
            }
        });
    }

    setInterval(autoIncrementEggs, 1000);
})();

function checkForWhiteChicken() {
    return fetch('/game/chicken/check_white_chicken.php')
        .then(response => response.json())
        .then(data => data.hasWhithicken);
}

function spawnWhiteEgg() {
    checkForWhiteChicken().then(hasWhiteChicken => {
        if (!hasWhiteChicken) return;
        const chance = 0.05;
        if (Math.random() < chance) {
            const whiteEgg = document.createElement('img');
            whiteEgg.src = '/resources/images/eggs/white_egg.png';
            whiteEgg.style.position = 'absolute';
            whiteEgg.style.width = '50px';
            whiteEgg.style.height = '50px';
            whiteEgg.style.cursor = 'pointer';
            whiteEgg.style.transition = 'opacity 0.5s ease-out'; // Transition douce
            whiteEgg.style.opacity = '1';

            // Position aléatoire
            whiteEgg.style.left = Math.random() * window.innerWidth * 0.95 + 'px';
            whiteEgg.style.top = Math.random() * window.innerHeight * 0.95 + 'px';

            //orientation aléatoire
            whiteEgg.style.transform = `rotate(${Math.random() * 360}deg)`;

            document.body.appendChild(whiteEgg);

            // Ajouter l'event listener pour cliquer sur l'œuf
            whiteEgg.addEventListener('click', function (event) {
                fetch('/game/chicken/click_white_egg.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const scoreElements = document.querySelectorAll('.score');
                        if (scoreElements.length > 0) {
                            scoreElements.forEach(element => {
                                element.textContent = new Intl.NumberFormat().format(data.newScore) + ' œufs';
                            });
                        }

                        // Ajouter un effet d'explosion
                        const explosion = document.createElement('div');
                        explosion.classList.add('egg-explosion');
                        explosion.style.left = whiteEgg.style.left;
                        explosion.style.top = whiteEgg.style.top;
                        document.body.appendChild(explosion);

                        setTimeout(() => explosion.remove(), 500); // Supprime l'effet après 0.5s

                        //Ajouter le nombre de points
                        let increment = data.increment;
                        let floatingText = document.createElement("span");
                        floatingText.textContent = `+${increment}`;
                        floatingText.classList.add("floating-text");

                        // Positionner l'élément au niveau du clic
                        floatingText.style.left = `${event.clientX}px`;
                        floatingText.style.top = `${event.clientY}px`;

                        document.body.appendChild(floatingText);

                        // Supprimer l'élément après l'animation
                        setTimeout(() => {
                            floatingText.remove();
                        }, 1000); // Correspond à la durée de l'animation

                    }
                    
                    whiteEgg.remove();
                });
        });


            // Disparition automatique après 5 secondes avec transition
            setTimeout(() => {
                whiteEgg.style.opacity = '0';
                setTimeout(() => {
                    if (whiteEgg.parentNode) {
                        whiteEgg.remove();
                    }
                }, 500); // Attend la fin de la transition
            }, 5000);
        }
    });
}

// Exécuter chaque seconde pour voir si un œuf apparaît
setInterval(spawnWhiteEgg, 1000);
