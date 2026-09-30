<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


$id_producto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_producto) {

    header("Location: index.php");
    exit;

}


$sql = "
    SELECT estado
    FROM productos
    WHERE id = ?
    LIMIT 1
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_producto
);

$stmt->execute();

$resultado = $stmt->get_result();

$producto = $resultado->fetch_assoc();


if (!$producto) {

    header("Location: index.php");
    exit;

}


$nuevo_estado =
    $producto["estado"] === "activo"
        ? "inactivo"
        : "activo";


$sql_actualizar = "
    UPDATE productos

    SET estado = ?

    WHERE id = ?
";


$stmt_actualizar =
    $conexion->prepare($sql_actualizar);


$stmt_actualizar->bind_param(
    "si",
    $nuevo_estado,
    $id_producto
);


$stmt_actualizar->execute();


header(
    "Location: index.php?estado=actualizado"
);

exit;