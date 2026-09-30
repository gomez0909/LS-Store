<?php

require_once __DIR__ . "/includes/auth_admin.php";
require_once __DIR__ . "/../config/conexion.php";


/* =========================================================
   TOTAL DE PRODUCTOS
   ========================================================= */

$sql_productos = "
    SELECT COUNT(*) AS total
    FROM productos
";

$resultado_productos =
    $conexion->query($sql_productos);

$total_productos =
    (int) $resultado_productos
        ->fetch_assoc()["total"];


/* =========================================================
   TOTAL DE PEDIDOS
   ========================================================= */

$sql_pedidos = "
    SELECT COUNT(*) AS total
    FROM pedidos
";

$resultado_pedidos =
    $conexion->query($sql_pedidos);

$total_pedidos =
    (int) $resultado_pedidos
        ->fetch_assoc()["total"];


/* =========================================================
   PEDIDOS PENDIENTES
   ========================================================= */

$sql_pendientes = "
    SELECT COUNT(*) AS total
    FROM pedidos
    WHERE estado = 'pendiente'
";

$resultado_pendientes =
    $conexion->query($sql_pendientes);

$total_pendientes =
    (int) $resultado_pendientes
        ->fetch_assoc()["total"];


/* =========================================================
   CLIENTES
   ========================================================= */

$sql_clientes = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE rol = 'cliente'
";

$resultado_clientes =
    $conexion->query($sql_clientes);

$total_clientes =
    (int) $resultado_clientes
        ->fetch_assoc()["total"];


/* =========================================================
   VENTAS PROCESADAS
   ========================================================= */

/*
    No contamos pedidos pendientes ni cancelados.

    Solo:
    confirmado
    enviado
    entregado
*/

$sql_ventas = "
    SELECT
        COALESCE(SUM(total), 0) AS total

    FROM pedidos

    WHERE estado IN (
        'confirmado',
        'enviado',
        'entregado'
    )
";

$resultado_ventas =
    $conexion->query($sql_ventas);

$total_ventas =
    $resultado_ventas
        ->fetch_assoc()["total"];


/* =========================================================
   PEDIDOS RECIENTES
   ========================================================= */

$sql_recientes = "
    SELECT
        pedidos.id,
        pedidos.total,
        pedidos.estado,
        pedidos.fecha_pedido,

        usuarios.nombres,
        usuarios.apellidos

    FROM pedidos

    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id

    ORDER BY pedidos.fecha_pedido DESC

    LIMIT 5
";

$resultado_recientes =
    $conexion->query($sql_recientes);


/* =========================================================
   PRODUCTOS CON POCO STOCK
   ========================================================= */

/*
    Mostramos las tallas que tengan
    3 unidades o menos.
*/

$sql_stock_bajo = "
    SELECT
        productos.id,
        productos.nombre,

        producto_tallas.talla,
        producto_tallas.stock

    FROM producto_tallas

    INNER JOIN productos
        ON producto_tallas.producto_id =
           productos.id

    WHERE productos.estado = 'activo'

    AND producto_tallas.stock <= 3

    ORDER BY
        producto_tallas.stock ASC,
        productos.nombre ASC

    LIMIT 8
";

$resultado_stock_bajo =
    $conexion->query($sql_stock_bajo);

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
        Administrador | LS Store
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
         ===================================================== -->

    <?php

        require_once __DIR__ .
            "/includes/sidebar.php";

    ?>



    <!-- =====================================================
         CONTENIDO PRINCIPAL
         ===================================================== -->

    <main class="admin-main">


        <!-- =================================================
             CABECERA
             ================================================= -->

        <div class="admin-cabecera">


            <div>

                <p>
                    LS STORE
                </p>

                <h1>
                    Dashboard
                </h1>

            </div>


            <div class="admin-bienvenida">

                Hola,

                <strong>

                    <?php
                        echo htmlspecialchars(
                            $_SESSION["nombres"]
                        );
                    ?>

                </strong>

            </div>


        </div>



        <!-- =================================================
             ESTADÍSTICAS
             ================================================= -->

        <section class="admin-estadisticas">


            <!-- PRODUCTOS -->

            <article class="admin-card">


                <span>
                    Productos
                </span>


                <strong>

                    <?php
                        echo $total_productos;
                    ?>

                </strong>


                <a href="productos/index.php">

                    Gestionar productos

                </a>


            </article>



            <!-- PEDIDOS -->

            <article class="admin-card">


                <span>
                    Pedidos
                </span>


                <strong>

                    <?php
                        echo $total_pedidos;
                    ?>

                </strong>


                <a href="pedidos/index.php">

                    Ver pedidos

                </a>


            </article>



            <!-- PENDIENTES -->

            <article class="admin-card">


                <span>
                    Pedidos pendientes
                </span>


                <strong>

                    <?php
                        echo $total_pendientes;
                    ?>

                </strong>


                <a href="pedidos/index.php">

                    Revisar pedidos

                </a>


            </article>



            <!-- CLIENTES -->

            <article class="admin-card">


                <span>
                    Clientes
                </span>


                <strong>

                    <?php
                        echo $total_clientes;
                    ?>

                </strong>


                <p>
                    Usuarios registrados
                </p>


            </article>



            <!-- VENTAS -->

            <article class="admin-card admin-card-ventas">


                <span>
                    Ventas procesadas
                </span>


                <strong>

                    $<?php

                        echo number_format(
                            $total_ventas,
                            0,
                            ",",
                            "."
                        );

                    ?>

                </strong>


                <p>
                    Confirmados, enviados y entregados
                </p>


            </article>


        </section>



        <!-- =================================================
             INFORMACIÓN PRINCIPAL
             ================================================= -->

        <section class="admin-dashboard-grid">



            <!-- =============================================
                 PEDIDOS RECIENTES
                 ============================================= -->

            <div class="admin-dashboard-panel">


                <div class="admin-dashboard-panel-header">


                    <div>

                        <p>
                            Actividad
                        </p>

                        <h2>
                            Pedidos recientes
                        </h2>

                    </div>


                    <a href="pedidos/index.php">

                        Ver todos

                    </a>


                </div>



                <?php if (
                    $resultado_recientes->num_rows === 0
                ): ?>


                    <p class="admin-vacio">

                        Todavía no hay pedidos.

                    </p>


                <?php else: ?>


                    <div class="admin-pedidos-recientes">


                        <?php while (
                            $pedido =
                                $resultado_recientes
                                    ->fetch_assoc()
                        ): ?>


                            <article class="admin-pedido-reciente">


                                <div>


                                    <a
                                        href="pedidos/ver.php?id=<?php
                                            echo (int)
                                                $pedido["id"];
                                        ?>"
                                        class="admin-pedido-numero"
                                    >

                                        Pedido
                                        #<?php
                                            echo (int)
                                                $pedido["id"];
                                        ?>

                                    </a>


                                    <p>

                                        <?php
                                            echo htmlspecialchars(
                                                $pedido["nombres"]
                                                . " "
                                                . $pedido["apellidos"]
                                            );
                                        ?>

                                    </p>


                                    <small>

                                        <?php
                                            echo htmlspecialchars(
                                                $pedido[
                                                    "fecha_pedido"
                                                ]
                                            );
                                        ?>

                                    </small>


                                </div>



                                <div class="admin-pedido-reciente-final">


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


                                </div>


                            </article>


                        <?php endwhile; ?>


                    </div>


                <?php endif; ?>


            </div>



            <!-- =============================================
                 STOCK BAJO
                 ============================================= -->

            <div class="admin-dashboard-panel">


                <div class="admin-dashboard-panel-header">


                    <div>

                        <p>
                            Inventario
                        </p>

                        <h2>
                            Stock bajo
                        </h2>

                    </div>


                    <a href="productos/index.php">

                        Productos

                    </a>


                </div>



                <?php if (
                    $resultado_stock_bajo->num_rows === 0
                ): ?>


                    <p class="admin-vacio">

                        No hay productos con stock bajo.

                    </p>


                <?php else: ?>


                    <div class="admin-stock-lista">


                        <?php while (
                            $stock =
                                $resultado_stock_bajo
                                    ->fetch_assoc()
                        ): ?>


                            <article class="admin-stock-item">


                                <div>


                                    <a
                                        href="productos/editar.php?id=<?php
                                            echo (int)
                                                $stock["id"];
                                        ?>"
                                    >

                                        <?php
                                            echo htmlspecialchars(
                                                $stock["nombre"]
                                            );
                                        ?>

                                    </a>


                                    <span>

                                        Talla

                                        <?php
                                            echo htmlspecialchars(
                                                $stock["talla"]
                                            );
                                        ?>

                                    </span>


                                </div>



                                <strong
                                    class="<?php

                                        echo
                                            (int) $stock["stock"] === 0

                                                ? "admin-sin-stock"

                                                : "";

                                    ?>"
                                >

                                    <?php
                                        echo (int)
                                            $stock["stock"];
                                    ?>

                                    unidades

                                </strong>


                            </article>


                        <?php endwhile; ?>


                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>