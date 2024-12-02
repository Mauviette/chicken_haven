<?php

echo '<!-- Barre de navigation -->
    <div class="navbar">
    <div class="profile-section">
        <a href="/chicken_haven/game/my_profile/index" class="profile-link">
        <img src="/chicken_haven/resources/images/player_icon.png" alt="Profil" class="profile-icon">
        <span class="username">' . $_SESSION['displayname'] . '</span>
        </a>
    </div>
    </div>

    <!-- Barre latérale toujours visible -->
    <div class="sidebar">
    <ul>
        <li><a href="/chicken_haven/game/main/index">Accueil</a></li>
        <li><a href="/chicken_haven/game/hatchery/index">Couvoir</a></li>
        <li><a href="/chicken_haven/game/shop/index">Boutique</a></li>
        <li><a href="/chicken_haven/game/social/index">Social</a></li>
    </ul>
    </div>';