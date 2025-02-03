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
    profile_background_color VARCHAR(7) NOT NULL
);

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    eggs BIGINT NOT NULL,
    eggs_last_day BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

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

INSERT INTO chickens (name, image_url, rarity, effect, multiplier) VALUES
("Poule rousse", 'red', 'common', "+1 oeuf par clic pour chaque poule rousse.", 1),
("Poule noire", 'black', 'common', "+0.1 oeuf par seconde pour chaque poule noire.", 0.1),
("Poule blanche", 'white', 'rare', "Chance de faire apparaitre un oeuf blanc sur l'écran. \nCliquer sur l'oeuf blanc donne 100 oeufs pour chaque poule blanche.", 100),
("Canard", 'duck', 'epic', "+1 oeuf par seconde pour chaque canard.", 1);

CREATE TABLE incubators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_number INT CHECK (slot_number BETWEEN 1 AND 3),
    chicken_id INT NULL, -- NULL si la couveuse est vide
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chicken_id) REFERENCES chickens(id) ON DELETE SET NULL,
    UNIQUE (user_id, slot_number) -- Un utilisateur ne peut pas avoir deux fois le même emplacement
);

