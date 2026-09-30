<?php

session_start();

require_once __DIR__ . "/config/conexion.php";


/* =========================================================
   RECIBIR FILTROS
   ========================================================= */

$buscar =
    trim($_GET["buscar"] ?? "");


$categoria_filtro =
    filter_input(
        INPUT_GET,
        "categoria",
        FILTER_VALIDATE_INT
    );


if (!$categoria_filtro) {

    $categoria_filtro = 0;

}


$orden =
    $_GET["orden"] ?? "recientes";


/* =========================================================
   ORDENAMIENTO PERMITIDO
   ========================================================= */

$ordenamientos = [

    "recientes" =>
        "productos.id DESC",

    "precio_menor" =>
        "productos.precio ASC",

    "precio_mayor" =>
        "productos.precio DESC",

    "nombre" =>
        "productos.nombre ASC"

];


if (!isset($ordenamientos[$orden])) {

    $orden = "recientes";

}


$sql_orden =
    $ordenamientos[$orden];


/* =========================================================
   CONSULTAR CATEGORÍAS
   ========================================================= */

$sql_categorias = "
    SELECT
        id,
        nombre

    FROM categorias

    WHERE estado = 'activo'

    ORDER BY nombre ASC
";


$resultado_categorias =
    $conexion->query(
        $sql_categorias
    );


/* =========================================================
   CONSULTAR PRODUCTOS
   ========================================================= */

$sql_productos = "
    SELECT
        productos.id,
        productos.nombre,
        productos.descripcion,
        productos.precio,
        productos.imagen,
        productos.color,

        categorias.nombre AS categoria,

        (
            SELECT
                COALESCE(
                    SUM(producto_tallas.stock),
                    0
                )

            FROM producto_tallas

            WHERE producto_tallas.producto_id =
                  productos.id

        ) AS stock_total

    FROM productos

    INNER JOIN categorias
        ON productos.categoria_id =
           categorias.id

    WHERE productos.estado = 'activo'

    AND categorias.estado = 'activo'

    AND (
        ? = 0
        OR productos.categoria_id = ?
    )

    AND (
        ? = ''
        OR productos.nombre LIKE ?
        OR productos.descripcion LIKE ?
        OR productos.color LIKE ?
    )

    ORDER BY $sql_orden
";


$stmt_productos =
    $conexion->prepare(
        $sql_productos
    );


$patron_busqueda =
    "%" . $buscar . "%";


$stmt_productos->bind_param(
    "iissss",
    $categoria_filtro,
    $categoria_filtro,
    $buscar,
    $patron_busqueda,
    $patron_busqueda,
    $patron_busqueda
);


$stmt_productos->execute();


$resultado_productos =
    $stmt_productos->get_result();


$total_resultados =
    $resultado_productos->num_rows;

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
        Productos | LS Store
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<?php

require_once __DIR__ .
    "/includes/header.php";

?>


<main>


    <!-- =====================================================
         ENCABEZADO
         ===================================================== -->

    <section class="catalogo-encabezado">


        <p>
            LS STORE
        </p>


        <h1>
            Todos los productos
        </h1>


        <span>
            Encuentra tu próximo estilo.
        </span>


    </section>



    <!-- =====================================================
         CATÁLOGO
         ===================================================== -->

    <section class="catalogo">


        <!-- =============================================
             FILTROS
             ============================================= -->

        <form
            method="GET"
            action="productos.php"
            class="catalogo-filtros"
        >


            <!-- BUSCAR -->

            <div class="catalogo-campo catalogo-busqueda">


                <label for="buscar">

                    Buscar

                </label>


                <input
                    type="search"
                    id="buscar"
                    name="buscar"

                    placeholder="Camiseta, hoodie, negro..."

                    value="<?php
                        echo htmlspecialchars(
                            $buscar
                        );
                    ?>"
                >


            </div>



            <!-- CATEGORÍA -->

            <div class="catalogo-campo">


                <label for="categoria">

                    Categoría

                </label>


                <select
                    id="categoria"
                    name="categoria"
                >


                    <option value="0">

                        Todas

                    </option>


                    <?php while (
                        $categoria =
                            $resultado_categorias
                                ->fetch_assoc()
                    ): ?>


                        <option
                            value="<?php
                                echo (int)
                                    $categoria["id"];
                            ?>"

                            <?php

                            if (
                                $categoria_filtro
                                ===
                                (int)
                                $categoria["id"]
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php
                                echo htmlspecialchars(
                                    $categoria["nombre"]
                                );
                            ?>

                        </option>


                    <?php endwhile; ?>


                </select>


            </div>



            <!-- ORDEN -->

            <div class="catalogo-campo">


                <label for="orden">

                    Ordenar

                </label>


                <select
                    id="orden"
                    name="orden"
                >


                    <option
                        value="recientes"

                        <?php
                            if (
                                $orden === "recientes"
                            ) {
                                echo "selected";
                            }
                        ?>
                    >
                        Más recientes
                    </option>


                    <option
                        value="precio_menor"

                        <?php
                            if (
                                $orden === "precio_menor"
                            ) {
                                echo "selected";
                            }
                        ?>
                    >
                        Menor precio
                    </option>


                    <option
                        value="precio_mayor"

                        <?php
                            if (
                                $orden === "precio_mayor"
                            ) {
                                echo "selected";
                            }
                        ?>
                    >
                        Mayor precio
                    </option>


                    <option
                        value="nombre"

                        <?php
                            if (
                                $orden === "nombre"
                            ) {
                                echo "selected";
                            }
                        ?>
                    >
                        Nombre A-Z
                    </option>


                </select>


            </div>



            <!-- BOTÓN -->

            <button
                type="submit"
                class="btn-catalogo-filtrar"
            >

                Aplicar filtros

            </button>


            <!-- LIMPIAR -->

            <?php if (
                $buscar !== "" ||
                $categoria_filtro !== 0 ||
                $orden !== "recientes"
            ): ?>


                <a
                    href="productos.php"
                    class="catalogo-limpiar"
                >

                    Limpiar

                </a>


            <?php endif; ?>


        </form>



        <!-- =============================================
             RESULTADOS
             ============================================= -->

        <div class="catalogo-resultados-info">


            <p>

                <?php
                    echo (int)
                        $total_resultados;
                ?>

                <?php if (
                    $total_resultados === 1
                ): ?>

                    producto encontrado

                <?php else: ?>

                    productos encontrados

                <?php endif; ?>

            </p>


        </div>



        <!-- =============================================
             PRODUCTOS
             ============================================= -->

        <?php if (
            $total_resultados === 0
        ): ?>


            <div class="catalogo-vacio">


                <h2>
                    No encontramos productos
                </h2>


                <p>
                    Prueba con otra búsqueda
                    o elimina los filtros.
                </p>


                <a
                    href="productos.php"
                    class="btn-producto"
                >
                    Ver todos
                </a>


            </div>


        <?php else: ?>


            <div class="catalogo-grid">


                <?php while (
                    $producto =
                        $resultado_productos
                            ->fetch_assoc()
                ): ?>


                    <article class="catalogo-producto">


                        <!-- IMAGEN -->

                        <a
                            href="producto.php?id=<?php
                                echo (int)
                                    $producto["id"];
                            ?>"
                            class="catalogo-producto-imagen"
                        >


                            <?php if (
                                !empty(
                                    $producto["imagen"]
                                )
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
                                    ?>"
                                >


                            <?php endif; ?>



                            <?php if (
                                (int)
                                $producto["stock_total"]
                                <= 0
                            ): ?>


                                <span class="producto-agotado">

                                    Agotado

                                </span>


                            <?php endif; ?>


                        </a>



                        <!-- INFORMACIÓN -->

                        <div class="catalogo-producto-info">


                            <span class="catalogo-categoria">

                                <?php
                                    echo htmlspecialchars(
                                        $producto["categoria"]
                                    );
                                ?>

                            </span>



                            <h2>

                                <a
                                    href="producto.php?id=<?php
                                        echo (int)
                                            $producto["id"];
                                    ?>"
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            $producto["nombre"]
                                        );
                                    ?>

                                </a>

                            </h2>



                            <?php if (
                                !empty(
                                    $producto["color"]
                                )
                            ): ?>


                                <p class="catalogo-color">

                                    <?php
                                        echo htmlspecialchars(
                                            $producto["color"]
                                        );
                                    ?>

                                </p>


                            <?php endif; ?>



                            <div class="catalogo-producto-final">


                                <strong>

                                    $<?php
                                        echo number_format(
                                            $producto["precio"],
                                            0,
                                            ",",
                                            "."
                                        );
                                    ?>

                                </strong>


                                <a
                                    href="producto.php?id=<?php
                                        echo (int)
                                            $producto["id"];
                                    ?>"
                                    class="btn-producto"
                                >

                                    Ver producto

                                </a>


                            </div>


                        </div>


                    </article>


                <?php endwhile; ?>


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