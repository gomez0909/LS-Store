<?php

require_once __DIR__ . "/../../config/app.php";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION["user_id"])) {

    header("Location: " . BASE_URL . "/auth/login.php");
    exit;

}


if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "administrador"
) {

    header("Location: " . BASE_URL . "/index.php");
    exit;

}