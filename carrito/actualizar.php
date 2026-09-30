<?php

session_start();

require_once __DIR__ .
    "/../config/conexion.php";


/* =========================================================
   SOLO POST
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../carrito.php");
    exit;
}


/* =========================================================
   RECIBIR DATOS
   ========================================================= */

$clave =
    $_POST["clave"] ?? "";


$accion =
    $_POST["accion"] ?? "";


/* =========================================================
   VALIDAR
   ========================================================= */

if (
    $clave === "" ||
    !isset(
        $_SESSION["carrito"][$clave]
    ) ||
    !in_array(
        $accion,
        [
            "sumar",
            "restar"
        ],
        true
    )
) {

    header("Location: ../carrito.php");
    exit;
}


/* =========================================================
   DATOS DEL PRODUCTO
   ========================================================= */

$item =
    $_SESSION["carrito"][$clave];


$producto_id =
    (int) $item["id"];


$talla =
    $item["talla"];


$cantidad_actual =
    (int) $item["cantidad"];


/* =========================================================
   CONSULTAR STOCK REAL
   ========================================================= */

$sql = "
    SELECT
        producto_tallas.stock

    FROM producto_tallas

    INNER JOIN productos
        ON producto_tallas.producto_id =
           productos.id

    WHERE producto_tallas.producto_id = ?
    AND producto_tallas.talla = ?
    AND productos.estado = 'activo'

    LIMIT 1
";


$stmt =
    $conexion->prepare($sql);


$stmt->bind_param(
    "is",
    $producto_id,
    $talla
);


$stmt->execute();


$resultado =
    $stmt
    ->get_result()
    ->fetch_assoc();


/* =========================================================
   PRODUCTO O TALLA NO DISPONIBLE
   ========================================================= */

if (!$resultado) {


    header(
        "Location: ../carrito.php?aviso=agotado"
    );

    exit;
}


$stock =
    (int) $resultado["stock"];


/* =========================================================
   SUMAR
   ========================================================= */

if ($accion === "sumar") {


    if ($stock <= 0) {

        header(
            "Location: ../carrito.php?aviso=agotado"
        );

        exit;
    }


    if ($cantidad_actual >= $stock) {

        header(
            "Location: ../carrito.php?aviso=limite"
        );

        exit;
    }


    $_SESSION["carrito"][$clave]["cantidad"] =
        $cantidad_actual + 1;
}


/* =========================================================
   RESTAR
   ========================================================= */

if ($accion === "restar") {


    if ($cantidad_actual > 1) {


        $_SESSION["carrito"][$clave]["cantidad"] =
            $cantidad_actual - 1;
    } else {


        /*
            Si tenía una sola unidad y pulsa "-",
            dejamos cantidad = 1.

            Para quitarlo completamente
            ya existe el botón Eliminar.
        */


        $_SESSION["carrito"][$clave]["cantidad"] =
            1;
    }
}


/* =========================================================
   VOLVER
   ========================================================= */

header(
    "Location: ../carrito.php"
);

exit;
