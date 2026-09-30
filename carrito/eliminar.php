<?php

session_start();


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../carrito.php");
    exit;
}


$clave = $_POST["clave"] ?? "";


if (isset($_SESSION["carrito"][$clave])) {

    unset($_SESSION["carrito"][$clave]);

}


header("Location: ../carrito.php");
exit;

?>