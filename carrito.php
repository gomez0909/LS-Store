<?php

session_start();

require_once __DIR__ . "/config/conexion.php";


$carrito =
    $_SESSION["carrito"] ?? [];


$total = 0;


/* =========================================================
   CONSULTAR STOCK ACTUAL DE LOS PRODUCTOS DEL CARRITO
   ========================================================= */

$stock_actual = [];


if (!empty($carrito)) {


    $sql_stock = "
        SELECT
            producto_tallas.stock,
            productos.estado

        FROM producto_tallas

        INNER JOIN productos
            ON producto_tallas.producto_id =
               productos.id

        WHERE producto_tallas.producto_id = ?
        AND producto_tallas.talla = ?

        LIMIT 1
    ";


    $stmt_stock =
        $conexion->prepare(
            $sql_stock
        );


    foreach (
        $carrito
        as $clave => $item
    ) {


        $producto_id =
            (int) $item["id"];


        $talla =
            $item["talla"];


        $stmt_stock->bind_param(
            "is",
            $producto_id,
            $talla
        );


        $stmt_stock->execute();


        $resultado =
            $stmt_stock
            ->get_result()
            ->fetch_assoc();


        if (
            !$resultado ||
            $resultado["estado"] !== "activo"
        ) {


            $stock_actual[$clave] = 0;
        } else {


            $stock_actual[$clave] =
                (int) $resultado["stock"];
        }
    }
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
        Carrito | LS Store
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


    <main>


        <section class="carrito">


            <!-- =================================================
             CABECERA
             ================================================= -->

            <div class="carrito-titulo">


                <p>
                    LS STORE
                </p>


                <h1>
                    Tu carrito
                </h1>


            </div>



            <!-- =================================================
             MENSAJES
             ================================================= -->

            <?php if (
                isset($_GET["aviso"]) &&
                $_GET["aviso"] === "limite"
            ): ?>


                <div class="carrito-mensaje">

                    Ya alcanzaste el máximo de unidades
                    disponibles para esa talla.

                </div>


            <?php endif; ?>



            <?php if (
                isset($_GET["aviso"]) &&
                $_GET["aviso"] === "agotado"
            ): ?>


                <div class="carrito-mensaje">

                    Esa talla ya no tiene unidades disponibles.

                </div>


            <?php endif; ?>



            <!-- =================================================
             CARRITO VACÍO
             ================================================= -->

            <?php if (
                empty($carrito)
            ): ?>


                <div class="carrito-vacio">


                    <h2>
                        Tu carrito está vacío
                    </h2>


                    <p>
                        Explora nuestros productos
                        y encuentra algo para tu estilo.
                    </p>


                    <a
                        href="productos.php"
                        class="btn-producto">
                        Ver productos
                    </a>


                </div>


            <?php else: ?>


                <div class="carrito-grid">



                    <!-- =========================================
                     PRODUCTOS
                     ========================================= -->

                    <div class="carrito-productos">


                        <?php foreach (
                            $carrito
                            as $clave => $item
                        ): ?>


                            <?php

                            $subtotal =
                                $item["precio"] *
                                $item["cantidad"];


                            $total +=
                                $subtotal;


                            $stock =
                                $stock_actual[$clave]
                                ?? 0;


                            $sin_stock =
                                $stock <= 0;


                            $cantidad_supera_stock =
                                $item["cantidad"] > $stock;

                            ?>


                            <article class="carrito-item">


                                <!-- IMAGEN -->

                                <a
                                    href="producto.php?id=<?php
                                                            echo (int)
                                                            $item["id"];
                                                            ?>"
                                    class="carrito-item-imagen">


                                    <?php if (
                                        !empty($item["imagen"])
                                    ): ?>


                                        <img
                                            src="assets/img/<?php
                                                            echo htmlspecialchars(
                                                                $item["imagen"]
                                                            );
                                                            ?>"

                                            alt="<?php
                                                    echo htmlspecialchars(
                                                        $item["nombre"]
                                                    );
                                                    ?>">


                                    <?php endif; ?>


                                </a>



                                <!-- INFORMACIÓN -->

                                <div class="carrito-item-info">


                                    <h2>


                                        <a
                                            href="producto.php?id=<?php
                                                                    echo (int)
                                                                    $item["id"];
                                                                    ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $item["nombre"]
                                            );
                                            ?>

                                        </a>


                                    </h2>



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

                                        Precio:

                                        <strong>

                                            $<?php
                                                echo number_format(
                                                    $item["precio"],
                                                    0,
                                                    ",",
                                                    "."
                                                );
                                                ?>

                                        </strong>

                                    </p>



                                    <!-- STOCK -->

                                    <?php if (
                                        $sin_stock
                                    ): ?>


                                        <p class="carrito-stock carrito-stock-error">

                                            Esta talla está agotada.

                                        </p>


                                    <?php elseif (
                                        $cantidad_supera_stock
                                    ): ?>


                                        <p class="carrito-stock carrito-stock-error">

                                            Solo quedan

                                            <?php
                                            echo $stock;
                                            ?>

                                            unidades disponibles.

                                            Reduce la cantidad
                                            antes de finalizar la compra.

                                        </p>


                                    <?php elseif (
                                        $stock <= 3
                                    ): ?>


                                        <p class="carrito-stock carrito-stock-poco">

                                            Solo quedan

                                            <?php
                                            echo $stock;
                                            ?>

                                            unidades.

                                        </p>


                                    <?php else: ?>


                                        <p class="carrito-stock">

                                            <?php
                                            echo $stock;
                                            ?>

                                            unidades disponibles.

                                        </p>


                                    <?php endif; ?>



                                    <!-- CONTROLES -->

                                    <div class="carrito-item-acciones">


                                        <form
                                            action="carrito/actualizar.php"
                                            method="POST"
                                            class="control-cantidad">


                                            <input
                                                type="hidden"
                                                name="clave"

                                                value="<?php
                                                        echo htmlspecialchars(
                                                            $clave
                                                        );
                                                        ?>">



                                            <!-- RESTAR -->

                                            <button
                                                type="submit"
                                                name="accion"
                                                value="restar"
                                                aria-label="Restar una unidad">
                                                −
                                            </button>



                                            <!-- CANTIDAD -->

                                            <span>

                                                <?php
                                                echo (int)
                                                $item["cantidad"];
                                                ?>

                                            </span>



                                            <!-- SUMAR -->

                                            <button
                                                type="submit"
                                                name="accion"
                                                value="sumar"

                                                aria-label="Agregar una unidad"

                                                <?php

                                                if (
                                                    $sin_stock ||
                                                    $item["cantidad"]
                                                    >= $stock
                                                ) {

                                                    echo "disabled";
                                                }

                                                ?>>
                                                +
                                            </button>


                                        </form>



                                        <!-- ELIMINAR -->

                                        <form
                                            action="carrito/eliminar.php"
                                            method="POST">


                                            <input
                                                type="hidden"
                                                name="clave"

                                                value="<?php
                                                        echo htmlspecialchars(
                                                            $clave
                                                        );
                                                        ?>">


                                            <button
                                                type="submit"
                                                class="btn-eliminar">
                                                Eliminar
                                            </button>


                                        </form>


                                    </div>


                                </div>



                                <!-- SUBTOTAL -->

                                <div class="carrito-item-subtotal">


                                    <span>
                                        Subtotal
                                    </span>


                                    <strong>

                                        $<?php
                                            echo number_format(
                                                $subtotal,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                    </strong>


                                </div>


                            </article>


                        <?php endforeach; ?>


                    </div>



                    <!-- =========================================
                     RESUMEN
                     ========================================= -->

                    <aside class="carrito-resumen">


                        <h2>
                            Resumen
                        </h2>



                        <div class="carrito-resumen-linea">


                            <span>
                                Productos
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



                        <div class="carrito-resumen-linea">


                            <span>
                                Envío
                            </span>


                            <strong>
                                Por definir
                            </strong>


                        </div>



                        <div class="carrito-total">


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



                        <?php

                        $carrito_valido = true;


                        foreach (
                            $carrito
                            as $clave => $item
                        ) {


                            $stock =
                                $stock_actual[$clave]
                                ?? 0;


                            if (
                                $stock <= 0 ||
                                $item["cantidad"] > $stock
                            ) {

                                $carrito_valido = false;

                                break;
                            }
                        }

                        ?>



                        <?php if (
                            $carrito_valido
                        ): ?>


                            <a
                                href="checkout.php"
                                class="btn-finalizar">
                                Finalizar compra
                            </a>


                        <?php else: ?>


                            <button
                                type="button"
                                class="btn-finalizar btn-finalizar-disabled"
                                disabled>

                                Revisa el inventario

                            </button>


                            <p class="carrito-advertencia">

                                Corrige los productos sin
                                disponibilidad antes de continuar.

                            </p>


                        <?php endif; ?>



                        <a
                            href="productos.php"
                            class="seguir-comprando">
                            Seguir comprando
                        </a>


                    </aside>


                </div>


            <?php endif; ?>


        </section>


    </main>


    <?php

    require_once __DIR__ .
        "/includes/footer.php";

    ?>


</body>

</html>