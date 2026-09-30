<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


$id_categoria = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_categoria) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   BUSCAR CATEGORÍA
   ========================================================= */

$sql_categoria = "
    SELECT
        id,
        estado

    FROM categorias

    WHERE id = ?

    LIMIT 1
";


$stmt_categoria =
    $conexion->prepare(
        $sql_categoria
    );


$stmt_categoria->bind_param(
    "i",
    $id_categoria
);


$stmt_categoria->execute();


$categoria =
    $stmt_categoria
        ->get_result()
        ->fetch_assoc();


if (!$categoria) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   SI ESTÁ ACTIVA, QUEREMOS DESACTIVARLA
   ========================================================= */

if ($categoria["estado"] === "activo") {


    /*
        Antes de desactivarla comprobamos
        si tiene productos activos.
    */

    $sql_productos = "
        SELECT COUNT(*) AS total

        FROM productos

        WHERE categoria_id = ?
        AND estado = 'activo'
    ";


    $stmt_productos =
        $conexion->prepare(
            $sql_productos
        );


    $stmt_productos->bind_param(
        "i",
        $id_categoria
    );


    $stmt_productos->execute();


    $resultado =
        $stmt_productos
            ->get_result()
            ->fetch_assoc();


    if ((int) $resultado["total"] > 0) {

        header(
            "Location: index.php?error=productos"
        );

        exit;

    }


    $nuevo_estado =
        "inactivo";

} else {


    $nuevo_estado =
        "activo";

}


/* =========================================================
   ACTUALIZAR
   ========================================================= */

$sql_actualizar = "
    UPDATE categorias

    SET estado = ?

    WHERE id = ?
";


$stmt_actualizar =
    $conexion->prepare(
        $sql_actualizar
    );


$stmt_actualizar->bind_param(
    "si",
    $nuevo_estado,
    $id_categoria
);


$stmt_actualizar->execute();


header(
    "Location: index.php?estado=actualizado"
);

exit;