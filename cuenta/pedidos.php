<?php

session_start();

require_once "../config/conexion.php";


if (!isset($_SESSION["user_id"])) {

    header(
        "Location: ../auth/login.php"
    );

    exit;
}


$usuario_id =
    (int) $_SESSION["user_id"];

$sql = "
    SELECT
        id,
        total,
        direccion,
        ciudad,
        departamento,
        estado,
        fecha_pedido

    FROM pedidos

    WHERE usuario_id = ?

    ORDER BY fecha_pedido DESC
";

$stmt =
    $conexion->prepare($sql);


$stmt->bind_param(
    "i",
    $usuario_id
);


$stmt->execute();


$resultado_pedidos =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Mis pedidos | LS Store</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css">

</head>


<body>


    <?php

    require_once dirname(__DIR__) . "/includes/header.php";

    ?>



    <main class="mis-pedidos">


        <div class="titulo-pedidos">

            <p>MI CUENTA</p>

            <h1>Mis pedidos</h1>

        </div>


        <?php if ($resultado_pedidos->num_rows === 0): ?>


            <div class="carrito-vacio">

                <h2>
                    Todavía no tienes pedidos
                </h2>

                <p>
                    Cuando realices una compra aparecerá aquí.
                </p>

                <a
                    href="../index.php"
                    class="btn-producto">
                    Ver productos
                </a>

            </div>


        <?php else: ?>


            <div class="lista-pedidos">


                <?php while (
                    $pedido =
                    $resultado_pedidos->fetch_assoc()
                ): ?>


                    <article class="pedido-card">


                        <div class="pedido-cabecera">

                            <div>

                                <p>Pedido</p>

                                <h2>
                                    #<?php
                                        echo (int)
                                        $pedido["id"];
                                        ?>
                                </h2>

                            </div>


                            <span class="estado-pedido">

                                <?php
                                echo htmlspecialchars(
                                    ucfirst(
                                        $pedido["estado"]
                                    )
                                );
                                ?>

                            </span>

                        </div>


                        <div class="pedido-datos">


                            <div>

                                <span>Fecha</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $pedido["fecha_pedido"]
                                    );
                                    ?>
                                </strong>

                            </div>


                            <div>

                                <span>Total</span>

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


                            <div>

                                <span>Entrega</span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $pedido["ciudad"]
                                    );
                                    ?>

                                </strong>

                            </div>


                        </div>


                        <a
                            href="pedido.php?id=<?php echo (int) $pedido["id"]; ?>"
                            class="btn-producto">
                            Ver pedido
                        </a>


                    </article>


                <?php endwhile; ?>


            </div>


        <?php endif; ?>


    </main>

    <?php

    require_once dirname(__DIR__) . "/includes/footer.php";

    ?>

</body>

</html>