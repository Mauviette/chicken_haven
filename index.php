<?php

if (!isset($_SESSION['username']))
{
    header("Location: login/index.php");
} else {
    header("Location: game/main/index.php");
}
