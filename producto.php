<?php

session_start();

require_once __DIR__ . "/config/conexion.php";


/* =========================================================
   VALIDAR ID DEL PRODUCTO
   ========================================================= */

$id_producto = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_producto) {

    header("Location: productos.php");
    exit;
}


/* =========================================================
   CONSULTAR PRODUCTO
   ========================================================= */

$sql_producto = "
    SELECT
        productos.id,
        productos.categoria_id,
        productos.nombre,
        productos.descripcion,
        productos.precio,
        productos.imagen,
        productos.color,

        categorias.nombre AS categoria

    FROM productos

    INNER JOIN categorias
        ON productos.categoria_id = categorias.id

    WHERE productos.id = ?
    AND productos.estado = 'activo'
    AND categorias.estado = 'activo'

    LIMIT 1
";


$stmt_producto =
    $conexion->prepare(
        $sql_producto
    );


$stmt_producto->bind_param(
    "i",
    $id_producto
);


$stmt_producto->execute();


$producto =
    $stmt_producto
    ->get_result()
    ->fetch_assoc();


if (!$producto) {

    header("Location: productos.php");
    exit;
}


/* =========================================================
   CONSULTAR TALLAS Y STOCK
   ========================================================= */

$sql_tallas = "
    SELECT
        talla,
        stock

    FROM producto_tallas

    WHERE producto_id = ?

    ORDER BY FIELD(
        talla,
        'S',
        'M',
        'L',
        'XL'
    )
";


$stmt_tallas =
    $conexion->prepare(
        $sql_tallas
    );


$stmt_tallas->bind_param(
    "i",
    $id_producto
);


$stmt_tallas->execute();


$resultado_tallas =
    $stmt_tallas->get_result();


$tallas = [];

$stock_total = 0;


while (
    $talla =
    $resultado_tallas->fetch_assoc()
) {

    $talla["stock"] =
        (int) $talla["stock"];


    $stock_total +=
        $talla["stock"];


    $tallas[] =
        $talla;
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
        <?php
        echo htmlspecialchars(
            $producto["nombre"]
        );
        ?>
        | LS Store
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


        <section class="detalle-producto">


            <!-- =================================================
             IMAGEN
             ================================================= -->

            <div class="detalle-imagen">


                <?php if (
                    !empty($producto["imagen"])
                ): ?>


                    <img
                        src="assets/img/<?php
                                        echo htmlspecialchars(
                                            $producto["imagen"]
                                        );
                                        ?>"

                        alt="<?php
                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );
                                ?>">


                <?php else: ?>


                    <div class="producto-sin-imagen">

                        Sin imagen

                    </div>


                <?php endif; ?>



                <?php if (
                    $stock_total <= 0
                ): ?>


                    <span class="detalle-etiqueta-agotado">

                        Agotado

                    </span>


                <?php endif; ?>


            </div>



            <!-- =================================================
             INFORMACIÓN
             ================================================= -->

            <div class="detalle-info">


                <!-- CATEGORÍA -->

                <a
                    href="productos.php?categoria=<?php
                                                    echo (int)
                                                    $producto["categoria_id"];
                                                    ?>"
                    class="detalle-categoria">

                    <?php
                    echo htmlspecialchars(
                        $producto["categoria"]
                    );
                    ?>

                </a>



                <!-- NOMBRE -->

                <h1>

                    <?php
                    echo htmlspecialchars(
                        $producto["nombre"]
                    );
                    ?>

                </h1>



                <!-- PRECIO -->

                <p class="detalle-precio">

                    $<?php
                        echo number_format(
                            $producto["precio"],
                            0,
                            ",",
                            "."
                        );
                        ?>

                </p>



                <!-- COLOR -->

                <?php if (
                    !empty($producto["color"])
                ): ?>


                    <div class="detalle-dato">


                        <span>
                            Color
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $producto["color"]
                            );
                            ?>

                        </strong>


                    </div>


                <?php endif; ?>



                <!-- DESCRIPCIÓN -->

                <div class="detalle-descripcion">


                    <h2>
                        Descripción
                    </h2>


                    <p>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $producto["descripcion"]
                            )
                        );
                        ?>

                    </p>


                </div>



                <!-- =================================================
                 STOCK DISPONIBLE
                 ================================================= -->

                <?php if (
                    $stock_total > 0
                ): ?>


                    <div class="detalle-stock-general">


                        <?php if (
                            $stock_total <= 5
                        ): ?>


                            <strong>
                                Últimas <?php echo $stock_total; ?> unidades disponibles
                            </strong>


                        <?php else: ?>


                            <span>
                                Producto disponible
                            </span>


                        <?php endif; ?>


                    </div>



                    <!-- =============================================
                     FORMULARIO
                     ============================================= -->

                    <form
                        action="carrito/agregar.php"
                        method="POST"
                        class="detalle-compra">


                        <input
                            type="hidden"
                            name="producto_id"

                            value="<?php
                                    echo (int)
                                    $producto["id"];
                                    ?>">



                        <div class="selector-tallas">


                            <div class="selector-tallas-titulo">


                                <strong>
                                    Selecciona tu talla
                                </strong>


                                <span>
                                    Stock por talla
                                </span>


                            </div>



                            <div class="tallas-opciones">


                                <?php foreach (
                                    $tallas as $talla
                                ): ?>


                                    <?php

                                    $sin_stock =
                                        $talla["stock"] <= 0;

                                    ?>


                                    <label
                                        class="talla-opcion <?php

                                                            if ($sin_stock) {

                                                                echo "talla-agotada";
                                                            }

                                                            ?>">


                                        <input
                                            type="radio"
                                            name="talla"

                                            value="<?php
                                                    echo htmlspecialchars(
                                                        $talla["talla"]
                                                    );
                                                    ?>"

                                            data-stock="<?php
                                                        echo (int)
                                                        $talla["stock"];
                                                        ?>"

                                            <?php
                                            echo $sin_stock
                                                ? "disabled"
                                                : "";
                                            ?>

                                            required>



                                        <span class="talla-nombre">

                                            <?php
                                            echo htmlspecialchars(
                                                $talla["talla"]
                                            );
                                            ?>

                                        </span>



                                        <small>


                                            <?php if (
                                                $sin_stock
                                            ): ?>

                                                Agotada

                                            <?php else: ?>

                                                <?php
                                                echo (int)
                                                $talla["stock"];
                                                ?>
                                                disponibles

                                            <?php endif; ?>


                                        </small>


                                    </label>


                                <?php endforeach; ?>


                            </div>


                        </div>



                        <!-- STOCK DE TALLA SELECCIONADA -->

                        <p
                            id="stock-seleccionado"
                            class="stock-seleccionado">

                            Selecciona una talla.

                        </p>



                        <button
                            type="submit"
                            class="btn-agregar-carrito">

                            Agregar al carrito

                        </button>


                    </form>


                <?php else: ?>


                    <!-- =============================================
                     PRODUCTO TOTALMENTE AGOTADO
                     ============================================= -->

                    <div class="detalle-sin-stock">


                        <strong>
                            Producto agotado
                        </strong>


                        <p>
                            Actualmente no hay unidades
                            disponibles en ninguna talla.
                        </p>


                        <a
                            href="productos.php"
                            class="btn-producto">

                            Ver otros productos

                        </a>


                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


    <?php

    require_once __DIR__ .
        "/includes/footer.php";

    ?>


    <!-- =========================================================
     MOSTRAR STOCK AL ELEGIR TALLA
     ========================================================= -->

    <script>
        const tallas =
            document.querySelectorAll(
                'input[name="talla"]'
            );


        const textoStock =
            document.getElementById(
                "stock-seleccionado"
            );


        tallas.forEach(function(talla) {


            talla.addEventListener(
                "change",
                function() {


                    const stock =
                        parseInt(
                            this.dataset.stock
                        );


                    if (stock === 1) {

                        textoStock.textContent =
                            "Queda 1 unidad en talla " +
                            this.value + ".";

                    } else {

                        textoStock.textContent =
                            "Quedan " +
                            stock +
                            " unidades en talla " +
                            this.value +
                            ".";

                    }

                }
            );

        });
    </script>


</body>

</html>