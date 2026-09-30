<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


/* =========================================================
   VALIDAR ID DEL PEDIDO
   ========================================================= */

$id_pedido = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_pedido) {

    header("Location: index.php");
    exit;
}


/* =========================================================
   BUSCAR PEDIDO
   ========================================================= */

$sql_pedido = "
    SELECT
        pedidos.id,
        pedidos.usuario_id,
        pedidos.total,
        pedidos.direccion,
        pedidos.ciudad,
        pedidos.departamento,
        pedidos.telefono,
        pedidos.estado,
        pedidos.fecha_pedido,

        usuarios.nombres,
        usuarios.apellidos,
        usuarios.correo

    FROM pedidos

    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id

    WHERE pedidos.id = ?

    LIMIT 1
";


$stmt_pedido =
    $conexion->prepare(
        $sql_pedido
    );


$stmt_pedido->bind_param(
    "i",
    $id_pedido
);


$stmt_pedido->execute();


$resultado_pedido =
    $stmt_pedido->get_result();


$pedido =
    $resultado_pedido->fetch_assoc();


/* =========================================================
   SI EL PEDIDO NO EXISTE
   ========================================================= */

if (!$pedido) {

    header("Location: index.php");
    exit;
}


/* =========================================================
   BUSCAR PRODUCTOS DEL PEDIDO
   ========================================================= */

$sql_detalles = "
    SELECT
        detalle_pedido.id,
        detalle_pedido.producto_id,
        detalle_pedido.talla,
        detalle_pedido.cantidad,
        detalle_pedido.precio_unitario,
        detalle_pedido.subtotal,

        productos.nombre,
        productos.imagen

    FROM detalle_pedido

    INNER JOIN productos
        ON detalle_pedido.producto_id =
           productos.id

    WHERE detalle_pedido.pedido_id = ?

    ORDER BY detalle_pedido.id ASC
";


$stmt_detalles =
    $conexion->prepare(
        $sql_detalles
    );


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
        content="width=device-width, initial-scale=1.0">

    <title>
        Pedido #<?php echo (int) $pedido["id"]; ?> | Administrador
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css">

</head>


<body>


    <div class="admin-layout">


        <!-- =====================================================
         SIDEBAR
         ===================================================== -->

        <?php

        require_once __DIR__ .
            "/../includes/sidebar.php";

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
                        Pedido #<?php echo (int) $pedido["id"]; ?>
                    </h1>

                </div>


                <a
                    href="index.php"
                    class="admin-btn-secundario">
                    Volver
                </a>


            </div>



            <!-- =================================================
             MENSAJE DE ESTADO ACTUALIZADO
             ================================================= -->

            <?php if (
                isset($_GET["estado"]) &&
                $_GET["estado"] === "actualizado"
            ): ?>


                <div class="admin-mensaje">

                    Estado del pedido actualizado correctamente.

                    <?php if (
                        isset($_GET["error"]) &&
                        $_GET["error"] === "estado"
                    ): ?>

                        <div class="admin-mensaje">

                            El cambio de estado solicitado no está permitido.

                        </div>

                    <?php endif; ?>


                    <?php if (
                        isset($_GET["error"]) &&
                        $_GET["error"] === "actualizacion"
                    ): ?>

                        <div class="admin-mensaje">

                            No fue posible actualizar el pedido.

                        </div>

                    <?php endif; ?>

                </div>


            <?php endif; ?>



            <!-- =================================================
             CONTENIDO DEL PEDIDO
             ================================================= -->

            <div class="admin-pedido-grid">



                <!-- =============================================
                 PRODUCTOS
                 ============================================= -->

                <section class="admin-pedido-productos">


                    <h2>
                        Productos
                    </h2>


                    <?php if (
                        $resultado_detalles->num_rows === 0
                    ): ?>


                        <p>
                            Este pedido no tiene productos registrados.
                        </p>


                    <?php else: ?>


                        <?php while (
                            $item =
                            $resultado_detalles->fetch_assoc()
                        ): ?>


                            <article class="admin-pedido-producto">



                                <!-- IMAGEN -->

                                <?php if (
                                    !empty($item["imagen"])
                                ): ?>


                                    <img
                                        src="../../assets/img/<?php
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



                                <!-- INFORMACIÓN -->

                                <div class="admin-pedido-producto-info">


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



                                <!-- SUBTOTAL -->

                                <div class="admin-pedido-subtotal">


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


                    <?php endif; ?>


                </section>



                <!-- =============================================
                 INFORMACIÓN DEL PEDIDO
                 ============================================= -->

                <aside class="admin-pedido-resumen">


                    <h2>
                        Información del pedido
                    </h2>



                    <!-- CLIENTE -->

                    <div class="admin-pedido-dato">


                        <span>
                            Cliente
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["nombres"] .
                                    " " .
                                    $pedido["apellidos"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- CORREO -->

                    <div class="admin-pedido-dato">


                        <span>
                            Correo
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["correo"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- TELÉFONO -->

                    <div class="admin-pedido-dato">


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



                    <!-- DIRECCIÓN -->

                    <div class="admin-pedido-dato">


                        <span>
                            Dirección de entrega
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["direccion"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- CIUDAD -->

                    <div class="admin-pedido-dato">


                        <span>
                            Ciudad
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["ciudad"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- DEPARTAMENTO -->

                    <div class="admin-pedido-dato">


                        <span>
                            Departamento
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["departamento"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- FECHA -->

                    <div class="admin-pedido-dato">


                        <span>
                            Fecha del pedido
                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $pedido["fecha_pedido"]
                            );
                            ?>

                        </strong>


                    </div>



                    <!-- ESTADO ACTUAL -->

                    <div class="admin-pedido-dato">


                        <span>
                            Estado actual
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



                    <!-- =========================================
                     CAMBIAR ESTADO
                     ========================================= -->

                    <?php

                    $opciones_estado = [

                        "pendiente" => [
                            "confirmado" => "Confirmado",
                            "cancelado" => "Cancelado"
                        ],

                        "confirmado" => [
                            "enviado" => "Enviado",
                            "cancelado" => "Cancelado"
                        ],

                        "enviado" => [
                            "entregado" => "Entregado"
                        ],

                        "entregado" => [],

                        "cancelado" => []

                    ];

                    ?>


                    <?php if (
                        !empty($opciones_estado[$pedido["estado"]])
                    ): ?>


                        <form
                            action="estado.php"
                            method="POST"
                            class="admin-estado-form">


                            <input
                                type="hidden"
                                name="pedido_id"
                                value="<?php echo (int) $pedido["id"]; ?>">


                            <label for="estado">
                                Cambiar estado
                            </label>


                            <select
                                id="estado"
                                name="estado"
                                required>


                                <option value="">
                                    Selecciona el nuevo estado
                                </option>


                                <?php foreach (
                                    $opciones_estado[$pedido["estado"]]
                                    as $valor => $texto
                                ): ?>


                                    <option
                                        value="<?php echo htmlspecialchars($valor); ?>">

                                        <?php echo htmlspecialchars($texto); ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>


                            <button
                                type="submit"
                                class="admin-btn-principal">
                                Actualizar estado
                            </button>


                        </form>


                    <?php else: ?>


                        <div class="admin-estado-form">

                            <p class="admin-estado-final">

                                Este pedido se encuentra en un estado final
                                y ya no puede modificarse.

                            </p>

                        </div>


                    <?php endif; ?>



                    <!-- =========================================
                     TOTAL
                     ========================================= -->

                    <div class="admin-pedido-total">


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


                </aside>


            </div>


        </main>


    </div>


</body>

</html>