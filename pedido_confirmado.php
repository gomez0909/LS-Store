<?php

session_start();


if (
    !isset($_SESSION["ultimo_pedido_id"])
) {

    header("Location: index.php");
    exit;
}


$pedido_id =
    $_SESSION["ultimo_pedido_id"];

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Pedido confirmado | LS Store</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css">

</head>


<body>

    <?php

    require_once __DIR__ . "/includes/header.php";

    ?>

    <main class="pedido-confirmado">

        <section>

            <p class="checkout-etiqueta">
                LS STORE
            </p>

            <h1>
                Pedido recibido
            </h1>

            <p>
                Tu pedido
                <strong>
                    #<?php echo (int) $pedido_id; ?>
                </strong>
                fue registrado correctamente.
            </p>

            <p>
                Puedes continuar navegando mientras
                procesamos tu compra.
            </p>

            <a
                href="index.php"
                class="btn-producto">
                Volver a la tienda
            </a>

        </section>

    </main>

    <?php

    require_once __DIR__ . "/includes/footer.php";

    ?>

</body>

</html>