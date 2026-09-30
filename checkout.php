<?php

session_start();

require_once __DIR__ . "/config/conexion.php";


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
   CONSULTAR USUARIO
   ========================================================= */

$sql_usuario = "
    SELECT
        id,
        nombres,
        apellidos,
        correo,
        telefono

    FROM usuarios

    WHERE id = ?
    AND estado = 'activo'

    LIMIT 1
";


$stmt_usuario =
    $conexion->prepare(
        $sql_usuario
    );


$stmt_usuario->bind_param(
    "i",
    $usuario_id
);


$stmt_usuario->execute();


$usuario =
    $stmt_usuario
    ->get_result()
    ->fetch_assoc();


if (!$usuario) {

    unset(
        $_SESSION["user_id"],
        $_SESSION["nombres"],
        $_SESSION["apellidos"],
        $_SESSION["correo"],
        $_SESSION["rol"]
    );


    header(
        "Location: auth/login.php?redirect=checkout"
    );

    exit;
}


/* =========================================================
   RECUPERAR DATOS DEL FORMULARIO SI HUBO ERROR
   ========================================================= */

$form_guardado =
    $_SESSION["checkout_form"] ?? [];


unset(
    $_SESSION["checkout_form"]
);


$direccion_form =
    $form_guardado["direccion"] ?? "";


$ciudad_form =
    $form_guardado["ciudad"] ?? "";


$departamento_form =
    $form_guardado["departamento"] ?? "";


$telefono_form =
    $form_guardado["telefono"]
    ?? $usuario["telefono"]
    ?? "";


/* =========================================================
   MENSAJES
   ========================================================= */

$mensaje_error = "";


if (isset($_GET["error"])) {


    if ($_GET["error"] === "datos") {

        $mensaje_error =
            "Revisa los datos de envío. Hay información incompleta o inválida.";
    }


    if ($_GET["error"] === "pedido") {

        $mensaje_error =
            "No fue posible procesar el pedido. Inténtalo nuevamente.";
    }
}


/* =========================================================
   CONSULTAR PRODUCTOS REALES DEL CARRITO
   ========================================================= */

$productos_checkout = [];

$total = 0;


$sql_producto = "
    SELECT
        productos.id,
        productos.nombre,
        productos.precio,
        productos.imagen,

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
";


$stmt_producto =
    $conexion->prepare(
        $sql_producto
    );


foreach (
    $carrito as $clave => $item
) {


    $producto_id =
        (int) $item["id"];


    $talla =
        $item["talla"];


    $cantidad =
        (int) $item["cantidad"];


    if ($cantidad <= 0) {

        header(
            "Location: carrito.php"
        );

        exit;
    }


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


    /* Producto eliminado, desactivado o talla inexistente */

    if (!$producto) {

        header(
            "Location: carrito.php?aviso=agotado"
        );

        exit;
    }


    $stock =
        (int) $producto["stock"];


    /* La cantidad supera el inventario actual */

    if (
        $stock <= 0 ||
        $cantidad > $stock
    ) {

        header(
            "Location: carrito.php?aviso=limite"
        );

        exit;
    }


    /* IMPORTANTE:
       usamos el precio actual de la base de datos,
       no el precio guardado en la sesión.
    */

    $precio =
        (float) $producto["precio"];


    $subtotal =
        $precio * $cantidad;


    $total +=
        $subtotal;


    $productos_checkout[] = [

        "id" =>
        (int) $producto["id"],

        "nombre" =>
        $producto["nombre"],

        "imagen" =>
        $producto["imagen"],

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

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Finalizar compra | LS Store
    </title>


    <link
        rel="stylesheet"
        href="assets/css/style.css">

</head>


<body>


    <?php

    require_once __DIR__ .
        "/includes/header.php";

    ?>


    <main class="checkout">


        <div class="checkout-contenedor">



            <!-- =================================================
             DATOS DE ENVÍO
             ================================================= -->

            <section class="checkout-datos">


                <p class="checkout-etiqueta">
                    LS STORE
                </p>


                <h1>
                    Finalizar compra
                </h1>



                <?php if (
                    $mensaje_error !== ""
                ): ?>


                    <div class="auth-mensaje">

                        <?php
                        echo htmlspecialchars(
                            $mensaje_error
                        );
                        ?>

                    </div>


                <?php endif; ?>



                <form
                    action="procesar_pedido.php"
                    method="POST"
                    class="checkout-form">



                    <!-- NOMBRE -->

                    <div class="campo">


                        <label>
                            Cliente
                        </label>


                        <input
                            type="text"

                            value="<?php
                                    echo htmlspecialchars(
                                        $usuario["nombres"]
                                            . " "
                                            . $usuario["apellidos"]
                                    );
                                    ?>"

                            disabled>


                    </div>



                    <!-- CORREO -->

                    <div class="campo">


                        <label>
                            Correo
                        </label>


                        <input
                            type="email"

                            value="<?php
                                    echo htmlspecialchars(
                                        $usuario["correo"]
                                    );
                                    ?>"

                            disabled>


                    </div>



                    <!-- TELÉFONO -->

                    <div class="campo">


                        <label for="telefono">

                            Teléfono

                        </label>


                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"

                            value="<?php
                                    echo htmlspecialchars(
                                        $telefono_form
                                    );
                                    ?>"

                            placeholder="Ej: 3159006525"

                            maxlength="20"

                            pattern="[0-9+() -]{7,20}"

                            required>


                    </div>



                    <!-- DIRECCIÓN -->

                    <div class="campo">


                        <label for="direccion">

                            Dirección de entrega

                        </label>


                        <input
                            type="text"
                            id="direccion"
                            name="direccion"

                            value="<?php
                                    echo htmlspecialchars(
                                        $direccion_form
                                    );
                                    ?>"

                            placeholder="Ej: Calle 75A #36-44"

                            minlength="5"
                            maxlength="255"

                            required>


                    </div>



                    <!-- CIUDAD -->

                    <div class="campo">


                        <label for="ciudad">

                            Ciudad

                        </label>


                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"

                            value="<?php
                                    echo htmlspecialchars(
                                        $ciudad_form
                                    );
                                    ?>"

                            placeholder="Ej: Medellín"

                            minlength="2"
                            maxlength="100"

                            required>


                    </div>



                    <!-- DEPARTAMENTO -->

                    <div class="campo">


                        <label for="departamento">

                            Departamento

                        </label>


                        <input
                            type="text"
                            id="departamento"
                            name="departamento"

                            value="<?php
                                    echo htmlspecialchars(
                                        $departamento_form
                                    );
                                    ?>"

                            placeholder="Ej: Antioquia"

                            minlength="2"
                            maxlength="100"

                            required>


                    </div>



                    <button
                        type="submit"
                        class="btn-auth">

                        Confirmar pedido

                    </button>


                </form>



                <a
                    href="carrito.php"
                    class="volver-carrito">

                    ← Volver al carrito

                </a>


            </section>



            <!-- =================================================
             RESUMEN
             ================================================= -->

            <aside class="checkout-resumen">


                <h2>
                    Tu pedido
                </h2>



                <?php foreach (
                    $productos_checkout as $item
                ): ?>


                    <article class="checkout-producto">


                        <div>


                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $item["nombre"]
                                );
                                ?>

                            </strong>


                            <p>

                                Talla:

                                <?php
                                echo htmlspecialchars(
                                    $item["talla"]
                                );
                                ?>

                                · Cantidad:

                                <?php
                                echo (int)
                                $item["cantidad"];
                                ?>

                            </p>


                        </div>



                        <strong>

                            $<?php

                                echo number_format(
                                    $item["subtotal"],
                                    0,
                                    ",",
                                    "."
                                );

                                ?>

                        </strong>


                    </article>


                <?php endforeach; ?>



                <div class="checkout-total">


                    <span>
                        Total
                    </span>


                    <strong>

                        $<?php

                            echo number_format(
                                $total,
                                0,
                                ",",
                                "."
                            );

                            ?>

                    </strong>


                </div>


            </aside>


        </div>


    </main>


    <?php

    require_once __DIR__ .
        "/includes/footer.php";

    ?>


</body>

</html>