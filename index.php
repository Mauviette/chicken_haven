<?php

if (!isset($_SESSION['username']))
{
    header("Location: login/index");
} else {
    header("Location: game/main/index");
}
