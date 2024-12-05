DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS scores;
DROP TABLE IF EXISTS friends;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    displayname VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    profile_icon_id INT NOT NULL,
    FOREIGN KEY (profile_icon_id) REFERENCES profile_icons(id),
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

CREATE TABLE profile_icons (
    id INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

INSERT INTO profile_icons (id, name, image_url) VALUES
(0, 'Black', 'black'),
(1, 'Red', 'red'),
(2, 'White', 'white');