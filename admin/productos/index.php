<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


/* =========================================================
   CONSULTAR PRODUCTOS
   ========================================================= */

$sql = "
    SELECT
        productos.id,
        productos.nombre,
        productos.precio,
        productos.imagen,
        productos.color,
        productos.estado,

        categorias.nombre AS categoria,

        COALESCE(
            SUM(producto_tallas.stock),
            0
        ) AS stock_total

    FROM productos

    INNER JOIN categorias
        ON productos.categoria_id =
           categorias.id

    LEFT JOIN producto_tallas
        ON productos.id =
           producto_tallas.producto_id

    GROUP BY
        productos.id,
        productos.nombre,
        productos.precio,
        productos.imagen,
        productos.color,
        productos.estado,
        categorias.nombre

    ORDER BY productos.id DESC
";


$resultado_productos =
    $conexion->query($sql);

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
        Productos | Administrador
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- SIDEBAR -->

    <?php

        require_once __DIR__ .
            "/../includes/sidebar.php";

    ?>



    <!-- CONTENIDO -->

    <main class="admin-main">



        <!-- CABECERA -->

        <div class="admin-cabecera">


            <div>

                <p>
                    LS STORE
                </p>

                <h1>
                    Productos
                </h1>

            </div>


            <a
                href="crear.php"
                class="admin-btn-principal"
            >
                + Nuevo producto
            </a>


        </div>



        <!-- =================================================
             MENSAJES
             ================================================= -->


        <!-- CREADO -->

        <?php if (
            isset($_GET["creado"]) &&
            $_GET["creado"] === "correcto"
        ): ?>


            <div class="admin-mensaje">

                Producto creado correctamente.

            </div>


        <?php endif; ?>



        <!-- EDITADO -->

        <?php if (
            isset($_GET["editado"]) &&
            $_GET["editado"] === "correcto"
        ): ?>


            <div class="admin-mensaje">

                Producto actualizado correctamente.

            </div>


        <?php endif; ?>



        <!-- ESTADO -->

        <?php if (
            isset($_GET["estado"]) &&
            $_GET["estado"] === "actualizado"
        ): ?>


            <div class="admin-mensaje">

                Estado del producto actualizado correctamente.

            </div>


        <?php endif; ?>



        <!-- =================================================
             TABLA
             ================================================= -->

        <section class="admin-panel">


            <div class="admin-tabla-contenedor">


                <table class="admin-tabla">


                    <!-- ENCABEZADOS -->

                    <thead>


                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Precio
                            </th>

                            <th>
                                Color
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>


                    </thead>



                    <!-- PRODUCTOS -->

                    <tbody>



                    <?php if (
                        $resultado_productos->num_rows === 0
                    ): ?>


                        <tr>

                            <td colspan="7">

                                No hay productos registrados.

                            </td>

                        </tr>


                    <?php else: ?>



                        <?php while (
                            $producto =
                                $resultado_productos->fetch_assoc()
                        ): ?>


                            <tr>



                                <!-- ========================
                                     PRODUCTO
                                     ======================== -->

                                <td>


                                    <div class="admin-producto">


                                        <?php if (
                                            !empty(
                                                $producto["imagen"]
                                            )
                                        ): ?>


                                            <img
                                                src="../../assets/img/<?php
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



                                        <div>


                                            <strong>

                                                <?php
                                                    echo htmlspecialchars(
                                                        $producto["nombre"]
                                                    );
                                                ?>

                                            </strong>


                                            <span>

                                                ID:

                                                <?php
                                                    echo (int)
                                                        $producto["id"];
                                                ?>

                                            </span>


                                        </div>


                                    </div>


                                </td>



                                <!-- ========================
                                     CATEGORÍA
                                     ======================== -->

                                <td>

                                    <?php
                                        echo htmlspecialchars(
                                            $producto["categoria"]
                                        );
                                    ?>

                                </td>



                                <!-- ========================
                                     PRECIO
                                     ======================== -->

                                <td>

                                    $<?php
                                        echo number_format(
                                            $producto["precio"],
                                            0,
                                            ",",
                                            "."
                                        );
                                    ?>

                                </td>



                                <!-- ========================
                                     COLOR
                                     ======================== -->

                                <td>

                                    <?php
                                        echo htmlspecialchars(
                                            $producto["color"]
                                            ?? ""
                                        );
                                    ?>

                                </td>



                                <!-- ========================
                                     STOCK
                                     ======================== -->

                                <td>

                                    <?php
                                        echo (int)
                                            $producto[
                                                "stock_total"
                                            ];
                                    ?>

                                </td>



                                <!-- ========================
                                     ESTADO
                                     ======================== -->

                                <td>


                                    <span
                                        class="admin-estado <?php

                                            echo
                                                $producto["estado"]
                                                === "activo"

                                                    ? "estado-activo"

                                                    : "estado-inactivo";

                                        ?>"
                                    >


                                        <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $producto["estado"]
                                                )
                                            );
                                        ?>


                                    </span>


                                </td>



                                <!-- ========================
                                     ACCIONES
                                     ======================== -->

                                <td>


                                    <div class="admin-acciones">


                                        <!-- EDITAR -->

                                        <a
                                            href="editar.php?id=<?php
                                                echo (int)
                                                    $producto["id"];
                                            ?>"
                                        >
                                            Editar
                                        </a>



                                        <!-- ACTIVAR / DESACTIVAR -->

                                        <form
                                            action="estado.php"
                                            method="POST"
                                        >


                                            <input
                                                type="hidden"
                                                name="id"

                                                value="<?php
                                                    echo (int)
                                                        $producto["id"];
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="admin-btn-link"
                                            >


                                                <?php if (
                                                    $producto["estado"]
                                                    === "activo"
                                                ): ?>


                                                    Desactivar


                                                <?php else: ?>


                                                    Activar


                                                <?php endif; ?>


                                            </button>


                                        </form>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


    </main>


</div>


</body>

</html>