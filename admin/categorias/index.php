<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


/* =========================================================
   CONSULTAR CATEGORÍAS
   ========================================================= */

$sql = "
    SELECT
        categorias.id,
        categorias.nombre,
        categorias.estado,

        COUNT(productos.id) AS total_productos

    FROM categorias

    LEFT JOIN productos
        ON categorias.id = productos.categoria_id

    GROUP BY
        categorias.id,
        categorias.nombre,
        categorias.estado

    ORDER BY categorias.nombre ASC
";


$resultado_categorias =
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
        Categorías | Administrador
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <?php

        require_once __DIR__ .
            "/../includes/sidebar.php";

    ?>


    <main class="admin-main">


        <!-- CABECERA -->

        <div class="admin-cabecera">


            <div>

                <p>
                    LS STORE
                </p>

                <h1>
                    Categorías
                </h1>

            </div>


            <a
                href="crear.php"
                class="admin-btn-principal"
            >
                + Nueva categoría
            </a>


        </div>



        <!-- MENSAJE CREACIÓN -->

        <?php if (
            isset($_GET["creado"]) &&
            $_GET["creado"] === "correcto"
        ): ?>

            <div class="admin-mensaje">
                Categoría creada correctamente.
            </div>

        <?php endif; ?>



        <!-- MENSAJE EDICIÓN -->

        <?php if (
            isset($_GET["editado"]) &&
            $_GET["editado"] === "correcto"
        ): ?>

            <div class="admin-mensaje">
                Categoría actualizada correctamente.
            </div>

        <?php endif; ?>



        <!-- MENSAJE ESTADO -->

        <?php if (
            isset($_GET["estado"]) &&
            $_GET["estado"] === "actualizado"
        ): ?>

            <div class="admin-mensaje">
                Estado de la categoría actualizado.
            </div>

        <?php endif; ?>



        <!-- ERROR: CATEGORÍA EN USO -->

        <?php if (
            isset($_GET["error"]) &&
            $_GET["error"] === "productos"
        ): ?>

            <div class="admin-mensaje">

                No puedes desactivar esta categoría
                porque contiene productos activos.

            </div>

        <?php endif; ?>



        <section class="admin-panel">


            <div class="admin-tabla-contenedor">


                <table class="admin-tabla">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Productos
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php if (
                        $resultado_categorias->num_rows === 0
                    ): ?>


                        <tr>

                            <td colspan="5">

                                No hay categorías registradas.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php while (
                            $categoria =
                                $resultado_categorias
                                    ->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                        echo (int)
                                            $categoria["id"];
                                    ?>

                                </td>



                                <!-- NOMBRE -->

                                <td>

                                    <strong>

                                        <?php
                                            echo htmlspecialchars(
                                                $categoria["nombre"]
                                            );
                                        ?>

                                    </strong>

                                </td>



                                <!-- PRODUCTOS -->

                                <td>

                                    <?php
                                        echo (int)
                                            $categoria[
                                                "total_productos"
                                            ];
                                    ?>

                                </td>



                                <!-- ESTADO -->

                                <td>


                                    <span
                                        class="admin-estado <?php

                                            echo
                                                $categoria["estado"]
                                                === "activo"

                                                ? "estado-activo"

                                                : "estado-inactivo";

                                        ?>"
                                    >

                                        <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $categoria["estado"]
                                                )
                                            );
                                        ?>

                                    </span>


                                </td>



                                <!-- ACCIONES -->

                                <td>


                                    <div class="admin-acciones">


                                        <a
                                            href="editar.php?id=<?php
                                                echo (int)
                                                    $categoria["id"];
                                            ?>"
                                        >
                                            Editar
                                        </a>



                                        <form
                                            action="estado.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)
                                                        $categoria["id"];
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="admin-btn-link"
                                            >

                                                <?php if (
                                                    $categoria["estado"]
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