<?php

session_start();

require_once __DIR__ . "/config/conexion.php";


/* =========================================================
   SOLO POST
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: checkout.php");
    exit;
}


/* =========================================================
   VERIFICAR SESIÓN
   ========================================================= */

if (!isset($_SESSION["user_id"])) {

    header(
        "Location: auth/login.php?redirect=checkout"
    );

    exit;
}


$usuario_id =
    (int) $_SESSION["user_id"];


/* =========================================================
   VERIFICAR CARRITO
   ========================================================= */

$carrito =
    $_SESSION["carrito"] ?? [];


if (empty($carrito)) {

    header("Location: carrito.php");
    exit;
}


/* =========================================================
   RECIBIR DATOS
   ========================================================= */

$direccion =
    trim($_POST["direccion"] ?? "");


$ciudad =
    trim($_POST["ciudad"] ?? "");


$departamento =
    trim($_POST["departamento"] ?? "");


$telefono =
    trim($_POST["telefono"] ?? "");


/* =========================================================
   GUARDAR TEMPORALMENTE LOS DATOS
   ========================================================= */

/*
    Si ocurre un error de validación,
    checkout.php podrá volver a llenar
    automáticamente el formulario.
*/

$_SESSION["checkout_form"] = [

    "direccion" =>
    $direccion,

    "ciudad" =>
    $ciudad,

    "departamento" =>
    $departamento,

    "telefono" =>
    $telefono

];


/* =========================================================
   VALIDAR DATOS DE ENVÍO
   ========================================================= */

$datos_validos = true;


if (
    strlen($direccion) < 5 ||
    strlen($direccion) > 255
) {

    $datos_validos = false;
}


if (
    strlen($ciudad) < 2 ||
    strlen($ciudad) > 100
) {

    $datos_validos = false;
}


if (
    strlen($departamento) < 2 ||
    strlen($departamento) > 100
) {

    $datos_validos = false;
}


/* Solo números y caracteres normales de teléfono */

if (
    !preg_match(
        '/^[0-9+() \-]{7,20}$/',
        $telefono
    )
) {

    $datos_validos = false;
}


if (!$datos_validos) {

    header(
        "Location: checkout.php?error=datos"
    );

    exit;
}


/* =========================================================
   ACTIVAR ERRORES DE MYSQLI
   ========================================================= */

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);


/* =========================================================
   INICIAR TRANSACCIÓN
   ========================================================= */

$conexion->begin_transaction();


try {


    $total = 0;


    $productos_pedido = [];


    /* =====================================================
       COMPROBAR CADA PRODUCTO
       ===================================================== */

    $sql_producto = "
        SELECT
            productos.id,
            productos.nombre,
            productos.precio,

            producto_tallas.stock

        FROM productos

        INNER JOIN producto_tallas
            ON productos.id =
               producto_tallas.producto_id

        INNER JOIN categorias
            ON productos.categoria_id =
               categorias.id

        WHERE productos.id = ?
        AND producto_tallas.talla = ?

        AND productos.estado = 'activo'
        AND categorias.estado = 'activo'

        LIMIT 1

        FOR UPDATE
    ";


    $stmt_producto =
        $conexion->prepare(
            $sql_producto
        );


    foreach (
        $carrito as $item
    ) {


        $producto_id =
            (int) $item["id"];


        $talla =
            trim($item["talla"]);


        $cantidad =
            (int) $item["cantidad"];


        if (
            $producto_id <= 0 ||
            $talla === "" ||
            $cantidad <= 0
        ) {

            throw new Exception(
                "carrito_invalido"
            );
        }



        /* CONSULTAR PRODUCTO */

        $stmt_producto->bind_param(
            "is",
            $producto_id,
            $talla
        );


        $stmt_producto->execute();


        $producto =
            $stmt_producto
            ->get_result()
            ->fetch_assoc();



        if (!$producto) {

            throw new Exception(
                "stock"
            );
        }



        $stock =
            (int) $producto["stock"];



        if (
            $stock <= 0 ||
            $cantidad > $stock
        ) {

            throw new Exception(
                "stock"
            );
        }



        /* PRECIO REAL DE LA BD */

        $precio =
            (float) $producto["precio"];


        $subtotal =
            $precio * $cantidad;


        $total +=
            $subtotal;



        $productos_pedido[] = [

            "producto_id" =>
            $producto_id,

            "talla" =>
            $talla,

            "cantidad" =>
            $cantidad,

            "precio" =>
            $precio,

            "subtotal" =>
            $subtotal

        ];
    }



    /* =====================================================
       CREAR PEDIDO
       ===================================================== */

    $estado =
        "pendiente";


    $sql_pedido = "
        INSERT INTO pedidos
        (
            usuario_id,
            total,
            direccion,
            ciudad,
            departamento,
            telefono,
            estado
        )

        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";


    $stmt_pedido =
        $conexion->prepare(
            $sql_pedido
        );


    $stmt_pedido->bind_param(
        "idsssss",
        $usuario_id,
        $total,
        $direccion,
        $ciudad,
        $departamento,
        $telefono,
        $estado
    );


    $stmt_pedido->execute();


    $pedido_id =
        $conexion->insert_id;



    /* =====================================================
       PREPARAR DETALLE
       ===================================================== */

    $sql_detalle = "
        INSERT INTO detalle_pedido
        (
            pedido_id,
            producto_id,
            talla,
            cantidad,
            precio_unitario,
            subtotal
        )

        VALUES (?, ?, ?, ?, ?, ?)
    ";


    $stmt_detalle =
        $conexion->prepare(
            $sql_detalle
        );



    /* =====================================================
       PREPARAR DESCUENTO DE STOCK
       ===================================================== */

    $sql_stock = "
        UPDATE producto_tallas

        SET stock = stock - ?

        WHERE producto_id = ?
        AND talla = ?
        AND stock >= ?
    ";


    $stmt_stock =
        $conexion->prepare(
            $sql_stock
        );



    /* =====================================================
       GUARDAR PRODUCTOS
       ===================================================== */

    foreach (
        $productos_pedido as $item
    ) {


        $producto_id =
            $item["producto_id"];


        $talla =
            $item["talla"];


        $cantidad =
            $item["cantidad"];


        $precio =
            $item["precio"];


        $subtotal =
            $item["subtotal"];



        /* DETALLE */

        $stmt_detalle->bind_param(
            "iisidd",
            $pedido_id,
            $producto_id,
            $talla,
            $cantidad,
            $precio,
            $subtotal
        );


        $stmt_detalle->execute();



        /* DESCONTAR INVENTARIO */

        $stmt_stock->bind_param(
            "iisi",
            $cantidad,
            $producto_id,
            $talla,
            $cantidad
        );


        $stmt_stock->execute();



        if (
            $stmt_stock->affected_rows !== 1
        ) {

            throw new Exception(
                "stock"
            );
        }
    }



    /* =====================================================
       ACTUALIZAR TELÉFONO DEL CLIENTE
       ===================================================== */

    $sql_usuario = "
        UPDATE usuarios

        SET telefono = ?

        WHERE id = ?
    ";


    $stmt_usuario =
        $conexion->prepare(
            $sql_usuario
        );


    $stmt_usuario->bind_param(
        "si",
        $telefono,
        $usuario_id
    );


    $stmt_usuario->execute();



    /* =====================================================
       CONFIRMAR TRANSACCIÓN
       ===================================================== */

    $conexion->commit();



    /* =====================================================
       LIMPIAR SESIÓN
       ===================================================== */

    unset(
        $_SESSION["carrito"],
        $_SESSION["checkout_form"]
    );


    $_SESSION["ultimo_pedido_id"] =
        $pedido_id;



    /* =====================================================
       CONFIRMACIÓN
       ===================================================== */

    header(
        "Location: pedido_confirmado.php"
    );

    exit;
} catch (Throwable $error) {


    $conexion->rollback();


    error_log(
        "Error al procesar pedido: " .
            $error->getMessage()
    );



    /* =============================================
       PROBLEMA DE INVENTARIO
       ============================================= */

    if (
        $error->getMessage()
        === "stock"
    ) {

        header(
            "Location: carrito.php?aviso=limite"
        );

        exit;
    }



    /* =============================================
       OTRO ERROR
       ============================================= */

    header(
        "Location: checkout.php?error=pedido"
    );

    exit;
}
