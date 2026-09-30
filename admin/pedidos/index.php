<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


/* =========================================================
   CONSULTAR PEDIDOS
   ========================================================= */

$sql = "
    SELECT
        pedidos.id,
        pedidos.total,
        pedidos.estado,
        pedidos.fecha_pedido,

        usuarios.nombres,
        usuarios.apellidos,
        usuarios.correo

    FROM pedidos

    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id

    ORDER BY pedidos.fecha_pedido DESC
";


$resultado_pedidos =
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

    <title>Pedidos | Administrador</title>

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
                    Pedidos
                </h1>

            </div>


        </div>



        <!-- MENSAJE DE ESTADO ACTUALIZADO -->

        <?php if (
            isset($_GET["estado"]) &&
            $_GET["estado"] === "actualizado"
        ): ?>


            <div class="admin-mensaje">

                Estado del pedido actualizado correctamente.

            </div>


        <?php endif; ?>



        <!-- TABLA -->

        <section class="admin-panel">


            <div class="admin-tabla-contenedor">


                <table class="admin-tabla">


                    <thead>


                        <tr>

                            <th>
                                Pedido
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Total
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
                        $resultado_pedidos->num_rows === 0
                    ): ?>


                        <tr>

                            <td colspan="6">

                                Todavía no hay pedidos registrados.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php while (
                            $pedido =
                                $resultado_pedidos->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PEDIDO -->

                                <td>

                                    <strong>

                                        #<?php
                                            echo (int)
                                                $pedido["id"];
                                        ?>

                                    </strong>

                                </td>



                                <!-- CLIENTE -->

                                <td>


                                    <div class="admin-cliente">


                                        <strong>

                                            <?php
                                                echo htmlspecialchars(
                                                    $pedido["nombres"] .
                                                    " " .
                                                    $pedido["apellidos"]
                                                );
                                            ?>

                                        </strong>


                                        <span>

                                            <?php
                                                echo htmlspecialchars(
                                                    $pedido["correo"]
                                                );
                                            ?>

                                        </span>


                                    </div>


                                </td>



                                <!-- FECHA -->

                                <td>

                                    <?php
                                        echo htmlspecialchars(
                                            $pedido["fecha_pedido"]
                                        );
                                    ?>

                                </td>



                                <!-- TOTAL -->

                                <td>

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

                                </td>



                                <!-- ESTADO -->

                                <td>


                                    <span
                                        class="admin-estado pedido-<?php
                                            echo htmlspecialchars(
                                                $pedido["estado"]
                                            );
                                        ?>"
                                    >

                                        <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $pedido["estado"]
                                                )
                                            );
                                        ?>

                                    </span>


                                </td>



                                <!-- ACCIONES -->

                                <td>


                                    <div class="admin-acciones">


                                        <a
                                            href="ver.php?id=<?php
                                                echo (int)
                                                    $pedido["id"];
                                            ?>"
                                        >
                                            Ver pedido
                                        </a>


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