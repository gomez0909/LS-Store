<?php

session_start();

require_once "../config/conexion.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}


$id_producto = filter_input(
    INPUT_POST,
    "producto_id",
    FILTER_VALIDATE_INT
);

$talla = trim($_POST["talla"] ?? "");


if (!$id_producto || $talla === "") {
    header("Location: ../index.php");
    exit;
}


$sql = "
    SELECT
        productos.id,
        productos.nombre,
        productos.precio,
        productos.imagen,
        producto_tallas.talla,
        producto_tallas.stock

    FROM productos

    INNER JOIN producto_tallas
        ON productos.id = producto_tallas.producto_id

    WHERE productos.id = ?
    AND producto_tallas.talla = ?
    AND productos.estado = 'activo'

    LIMIT 1
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "is",
    $id_producto,
    $talla
);

$stmt->execute();

$resultado = $stmt->get_result();

$producto = $resultado->fetch_assoc();


if (!$producto) {
    header("Location: ../index.php");
    exit;
}


if ($producto["stock"] <= 0) {
    header("Location: ../producto.php?id=" . $id_producto);
    exit;
}


if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}


$clave = $id_producto . "-" . $talla;


if (isset($_SESSION["carrito"][$clave])) {

    if ($_SESSION["carrito"][$clave]["cantidad"] < $producto["stock"]) {
        $_SESSION["carrito"][$clave]["cantidad"]++;
    }

} else {

    $_SESSION["carrito"][$clave] = [

        "id" => $producto["id"],

        "nombre" => $producto["nombre"],

        "precio" => $producto["precio"],

        "imagen" => $producto["imagen"],

        "talla" => $producto["talla"],

        "cantidad" => 1

    ];
}


header("Location: ../carrito.php");
exit;

?>