SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS scores;
DROP TABLE IF EXISTS friends;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS chickens;
DROP TABLE IF EXISTS user_chickens;
DROP TABLE IF EXISTS incubators;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE chickens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    rarity ENUM('common', 'rare', 'epic', 'legendary') NOT NULL DEFAULT 'common',
    effect VARCHAR(255) NOT NULL DEFAULT 'none',
    multiplier FLOAT NOT NULL DEFAULT 1
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    displayname VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    profile_icon_id INT NOT NULL DEFAULT 1,
    FOREIGN KEY (profile_icon_id) REFERENCES chickens(id),
    profile_background_color VARCHAR(7) NOT NULL,
    nb_cheater_alerts INT NOT NULL DEFAULT 0,
    cheater BOOLEAN NOT NULL DEFAULT 0,
    last_cheat_time TIMESTAMP,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
--ALTER TABLE users ADD COLUMN nb_cheater_alerts INT NOT NULL DEFAULT 0;
--ALTER TABLE users ADD COLUMN cheater BOOLEAN NOT NULL DEFAULT 0;
--ALTER TABLE users ADD COLUMN last_cheat_time TIMESTAMP;
--ALTER TABLE users ADD COLUMN last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP;


CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    eggs BIGINT NOT NULL,
    eggs_last_day BIGINT NOT NULL,
    eggs_earned_total BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    eggs_after_decimal FLOAT NOT NULL DEFAULT 0
);
--ALTER TABLE scores ADD COLUMN eggs_after_decimal FLOAT NOT NULL DEFAULT 0;
--ALTER TABLE scores ADD COLUMN eggs_earned_total BIGINT NOT NULL DEFAULT 0;


CREATE TABLE friends (

    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    PRIMARY KEY (user1_id, user2_id),
    FOREIGN KEY (user1_id) REFERENCES users(id),
    FOREIGN KEY (user2_id) REFERENCES users(id),
    accepted BOOLEAN NOT NULL
);

CREATE TABLE user_chickens (
    user_id INT NOT NULL,
    chicken_id INT NOT NULL,
    count INT NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, chicken_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chicken_id) REFERENCES chickens(id) ON DELETE CASCADE,
    UNIQUE(user_id, chicken_id)
);

CREATE TABLE incubators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_number INT CHECK (slot_number BETWEEN 1 AND 3),
    chicken_id INT NULL, -- NULL si la couveuse est vide
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chicken_id) REFERENCES chickens(id) ON DELETE SET NULL,
    UNIQUE (user_id, slot_number) -- Un utilisateur ne peut pas avoir deux fois le même emplacement
);

CREATE TABLE openable_eggs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    buyable BOOLEAN NOT NULL DEFAULT 1,
    price INT NOT NULL DEFAULT 0,
    price_mult FLOAT NOT NULL DEFAULT 1.1,
    limited BOOLEAN NOT NULL DEFAULT 0,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    probability_common FLOAT NOT NULL DEFAULT 0.70,
    probability_rare FLOAT NOT NULL DEFAULT 0.22,
    probability_epic FLOAT NOT NULL DEFAULT 0.07,
    probability_legendary FLOAT NOT NULL DEFAULT 0.01
);
--ALTER TABLE openable_eggs ADD COLUMN price_mult FLOAT NOT NULL DEFAULT 1.1;

CREATE TABLE egg_contents (
    egg_id INT NOT NULL,
    chicken_id INT NOT NULL,
    PRIMARY KEY (egg_id, chicken_id),
    FOREIGN KEY (egg_id) REFERENCES openable_eggs(id) ON DELETE CASCADE,
    FOREIGN KEY (chicken_id) REFERENCES chickens(id) ON DELETE CASCADE,
    rarity ENUM('common', 'rare', 'epic', 'legendary') NOT NULL
);


CREATE TABLE player_egg_price (
    user_id INT NOT NULL,
    egg_id INT NOT NULL,
    price INT NOT NULL,
    PRIMARY KEY (user_id, egg_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (egg_id) REFERENCES openable_eggs(id) ON DELETE CASCADE
);


-- Exemple d'ajout d'une poule à un utilisateur
--INSERT INTO user_chickens (user_id, chicken_id) VALUES (1, 1) ON DUPLICATE KEY UPDATE count = count + 1;

/* Ajout des poules */
INSERT INTO chickens (name, image_url, rarity, effect, multiplier) VALUES
("Poule rousse", 'red', 'common', "+0.5 oeuf par clic pour chaque poule rousse.", 0.5),
("Poule noire", 'black', 'common', "+0.1 oeuf par seconde pour chaque poule noire.", 0.1),
("Poule blanche", 'white', 'rare', "Chance de faire apparaitre un oeuf blanc sur l'écran. \nCliquer sur l'oeuf blanc donne 50 oeufs pour chaque poule blanche.", 50),
("Canard", 'duck', 'epic', "+1 oeuf par seconde pour chaque canard.", 1);


/* Ajout des oeufs */

-- Oeuf argenté
INSERT INTO openable_eggs (name, image_url, price, price_mult, probability_common, probability_rare, probability_epic, probability_legendary) VALUES
("Oeuf argenté", 'silver_egg', 500, 1.1, 0.70, 0.22, 0.08, 0.0);
--UPDATE openable_eggs SET price_mult = 1.1 WHERE name = 'Oeuf argenté';

INSERT INTO egg_contents (egg_id, chicken_id, rarity) VALUES
(1, 1, 'common'),
(1, 2, 'common'),
(1, 3, 'rare'),
(1, 4, 'epic');






/* Evenements */
CREATE EVENT reset_daily_scores
ON SCHEDULE EVERY 1 DAY
STARTS TIMESTAMP(CURRENT_DATE, '00:00:00')
DO
UPDATE scores SET eggs_last_day = 0;
