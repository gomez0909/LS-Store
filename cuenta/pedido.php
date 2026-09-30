<?php

session_start();

require_once "../config/conexion.php";


/* ==============================
   COMPROBAR SESIÓN
   ============================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


/* ==============================
   VALIDAR ID DEL PEDIDO
   ============================== */

$id_pedido = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_pedido) {

    header("Location: pedidos.php");
    exit;

}


$usuario_id = (int) $_SESSION["user_id"];


/* ==============================
   BUSCAR EL PEDIDO
   ============================== */

$sql_pedido = "
    SELECT
        id,
        total,
        direccion,
        ciudad,
        departamento,
        telefono,
        estado,
        fecha_pedido

    FROM pedidos

    WHERE id = ?
    AND usuario_id = ?

    LIMIT 1
";


$stmt_pedido = $conexion->prepare($sql_pedido);

$stmt_pedido->bind_param(
    "ii",
    $id_pedido,
    $usuario_id
);

$stmt_pedido->execute();

$resultado_pedido =
    $stmt_pedido->get_result();

$pedido =
    $resultado_pedido->fetch_assoc();


/* ==============================
   COMPROBAR QUE EL PEDIDO EXISTE
   ============================== */

if (!$pedido) {

    header("Location: pedidos.php");
    exit;

}


/* ==============================
   BUSCAR PRODUCTOS DEL PEDIDO
   ============================== */

$sql_detalles = "
    SELECT
        detalle_pedido.producto_id,
        detalle_pedido.talla,
        detalle_pedido.cantidad,
        detalle_pedido.precio_unitario,
        detalle_pedido.subtotal,

        productos.nombre,
        productos.imagen

    FROM detalle_pedido

    INNER JOIN productos
        ON detalle_pedido.producto_id = productos.id

    WHERE detalle_pedido.pedido_id = ?

    ORDER BY detalle_pedido.id ASC
";


$stmt_detalles =
    $conexion->prepare($sql_detalles);

$stmt_detalles->bind_param(
    "i",
    $id_pedido
);

$stmt_detalles->execute();

$resultado_detalles =
    $stmt_detalles->get_result();

?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Pedido #<?php echo (int) $pedido["id"]; ?> | LS Store
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<?php

require_once dirname(__DIR__) . "/includes/header.php";

?>


<main class="detalle-pedido">


    <div class="detalle-pedido-titulo">

        <p>MI CUENTA</p>

        <h1>
            Pedido #<?php echo (int) $pedido["id"]; ?>
        </h1>

        <span class="estado-pedido">

            <?php
                echo htmlspecialchars(
                    ucfirst($pedido["estado"])
                );
            ?>

        </span>

    </div>


    <div class="detalle-pedido-grid">


        <!-- PRODUCTOS -->

        <section class="pedido-productos">

            <h2>
                Productos
            </h2>


            <?php while (
                $item =
                    $resultado_detalles->fetch_assoc()
            ): ?>


                <article class="pedido-producto">


                    <img
                        src="../assets/img/<?php
                            echo htmlspecialchars(
                                $item["imagen"]
                            );
                        ?>"
                        alt="<?php
                            echo htmlspecialchars(
                                $item["nombre"]
                            );
                        ?>"
                    >


                    <div class="pedido-producto-info">


                        <h3>

                            <?php
                                echo htmlspecialchars(
                                    $item["nombre"]
                                );
                            ?>

                        </h3>


                        <p>

                            Talla:

                            <strong>
                                <?php
                                    echo htmlspecialchars(
                                        $item["talla"]
                                    );
                                ?>
                            </strong>

                        </p>


                        <p>

                            Cantidad:

                            <strong>
                                <?php
                                    echo (int)
                                        $item["cantidad"];
                                ?>
                            </strong>

                        </p>


                        <p>

                            Precio unitario:

                            <strong>

                                $<?php
                                    echo number_format(
                                        $item["precio_unitario"],
                                        0,
                                        ",",
                                        "."
                                    );
                                ?>

                            </strong>

                        </p>


                    </div>


                    <div class="pedido-producto-subtotal">

                        <span>
                            Subtotal
                        </span>

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

                    </div>


                </article>


            <?php endwhile; ?>


        </section>



        <!-- INFORMACIÓN DEL PEDIDO -->

        <aside class="pedido-resumen">


            <h2>
                Información del pedido
            </h2>


            <div class="pedido-resumen-dato">

                <span>
                    Fecha
                </span>

                <strong>
                    <?php
                        echo htmlspecialchars(
                            $pedido["fecha_pedido"]
                        );
                    ?>
                </strong>

            </div>


            <div class="pedido-resumen-dato">

                <span>
                    Estado
                </span>

                <strong>
                    <?php
                        echo htmlspecialchars(
                            ucfirst(
                                $pedido["estado"]
                            )
                        );
                    ?>
                </strong>

            </div>


            <div class="pedido-resumen-dato">

                <span>
                    Dirección
                </span>

                <strong>
                    <?php
                        echo htmlspecialchars(
                            $pedido["direccion"]
                        );
                    ?>
                </strong>

            </div>


            <div class="pedido-resumen-dato">

                <span>
                    Ciudad
                </span>

                <strong>

                    <?php
                        echo htmlspecialchars(
                            $pedido["ciudad"]
                        );
                    ?>,

                    <?php
                        echo htmlspecialchars(
                            $pedido["departamento"]
                        );
                    ?>

                </strong>

            </div>


            <div class="pedido-resumen-dato">

                <span>
                    Teléfono
                </span>

                <strong>
                    <?php
                        echo htmlspecialchars(
                            $pedido["telefono"]
                        );
                    ?>
                </strong>

            </div>


            <div class="pedido-resumen-total">

                <span>
                    Total
                </span>

                <strong>

                    $<?php
                        echo number_format(
                            $pedido["total"],
                            0,
                            ",",
                            "."
                        );
                    ?>

                </strong>

            </div>


            <a
                href="pedidos.php"
                class="btn-volver-pedidos"
            >
                Volver a mis pedidos
            </a>


        </aside>


    </div>


</main>


<?php

require_once dirname(__DIR__) . "/includes/footer.php";

?>


</body>

</html>