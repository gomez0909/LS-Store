<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


$id_pedido = filter_input(
    INPUT_POST,
    "pedido_id",
    FILTER_VALIDATE_INT
);


$nuevo_estado =
    trim($_POST["estado"] ?? "");


$estados_permitidos = [
    "pendiente",
    "confirmado",
    "enviado",
    "entregado",
    "cancelado"
];


if (
    !$id_pedido ||
    !in_array(
        $nuevo_estado,
        $estados_permitidos,
        true
    )
) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   REGLAS DE TRANSICIÓN
   ========================================================= */

$transiciones = [

    "pendiente" => [
        "confirmado",
        "cancelado"
    ],

    "confirmado" => [
        "enviado",
        "cancelado"
    ],

    "enviado" => [
        "entregado"
    ],

    "entregado" => [],

    "cancelado" => []

];


mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);


$conexion->begin_transaction();


try {


    /* =====================================================
       BLOQUEAR EL PEDIDO
       ===================================================== */

    $sql_pedido = "
        SELECT
            id,
            estado

        FROM pedidos

        WHERE id = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmt_pedido =
        $conexion->prepare(
            $sql_pedido
        );


    $stmt_pedido->bind_param(
        "i",
        $id_pedido
    );


    $stmt_pedido->execute();


    $pedido =
        $stmt_pedido
            ->get_result()
            ->fetch_assoc();


    if (!$pedido) {

        throw new Exception(
            "El pedido no existe."
        );

    }


    $estado_actual =
        $pedido["estado"];



    /* =====================================================
       MISMO ESTADO
       ===================================================== */

    if ($estado_actual === $nuevo_estado) {

        $conexion->commit();

        header(
            "Location: ver.php?id=" .
            $id_pedido
        );

        exit;

    }



    /* =====================================================
       VALIDAR TRANSICIÓN
       ===================================================== */

    if (
        !isset($transiciones[$estado_actual]) ||
        !in_array(
            $nuevo_estado,
            $transiciones[$estado_actual],
            true
        )
    ) {

        $conexion->rollback();

        header(
            "Location: ver.php?id=" .
            $id_pedido .
            "&error=estado"
        );

        exit;

    }



    /* =====================================================
       SI SE CANCELA, DEVOLVER INVENTARIO
       ===================================================== */

    if ($nuevo_estado === "cancelado") {


        $sql_detalles = "
            SELECT
                producto_id,
                talla,
                cantidad

            FROM detalle_pedido

            WHERE pedido_id = ?
        ";


        $stmt_detalles =
            $conexion->prepare(
                $sql_detalles
            );


        $stmt_detalles->bind_param(
            "i",
            $id_pedido
        );


        $stmt_detalles->execute();


        $resultado_detalles =
            $stmt_detalles->get_result();



        $sql_stock = "
            UPDATE producto_tallas

            SET stock = stock + ?

            WHERE producto_id = ?
            AND talla = ?
        ";


        $stmt_stock =
            $conexion->prepare(
                $sql_stock
            );



        while (
            $detalle =
                $resultado_detalles->fetch_assoc()
        ) {


            $cantidad =
                (int) $detalle["cantidad"];


            $producto_id =
                (int) $detalle["producto_id"];


            $talla =
                $detalle["talla"];



            $stmt_stock->bind_param(
                "iis",
                $cantidad,
                $producto_id,
                $talla
            );


            $stmt_stock->execute();



            if (
                $stmt_stock->affected_rows !== 1
            ) {

                throw new Exception(
                    "No fue posible devolver el stock."
                );

            }

        }

    }



    /* =====================================================
       ACTUALIZAR ESTADO
       ===================================================== */

    $sql_actualizar = "
        UPDATE pedidos

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
        $id_pedido
    );


    $stmt_actualizar->execute();



    /* =====================================================
       CONFIRMAR
       ===================================================== */

    $conexion->commit();


    header(
        "Location: ver.php?id=" .
        $id_pedido .
        "&estado=actualizado"
    );

    exit;



} catch (Throwable $error) {


    $conexion->rollback();


    error_log(
        "Error cambiando estado del pedido: " .
        $error->getMessage()
    );


    header(
        "Location: ver.php?id=" .
        $id_pedido .
        "&error=actualizacion"
    );

    exit;

}