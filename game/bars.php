<?php

echo '<!-- Barre de navigation -->
    <div class="navbar">
    <div class="profile-section">
        <a href="my_profile.php" class="profile-link">
        <img src="images/player_icon.png" alt="Profil" class="profile-icon">
        <span class="username">' . $_SESSION['displayname'] . '</span>
        </a>
    </div>
    </div>

    <!-- Barre latérale toujours visible -->
    <div class="sidebar">
    <ul>
        <li><a href="main.php">Accueil</a></li>
        <li><a href="hatchery.php">Couvoir</a></li>
        <li><a href="shop.php">Boutique</a></li>
        <li><a href="leaderboard.php">Classement</a></li>
    </ul>
    </div>';