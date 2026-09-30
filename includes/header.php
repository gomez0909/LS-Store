<?php

require_once __DIR__ . "/../config/app.php";

?>

<header>

    <div class="logo">

        <a href="<?php echo BASE_URL; ?>/index.php">

            <img
                src="<?php echo BASE_URL; ?>/assets/img/LSstore_logo.png"
                alt="Logo LS Store">

        </a>

    </div>


    <nav>

        <a href="<?php echo BASE_URL; ?>/index.php">
            Inicio
        </a>

        <a href="<?php echo BASE_URL; ?>/productos.php">
            Productos
        </a>

        <a href="<?php echo BASE_URL; ?>/index.php#nosotros">
            Nosotros
        </a>

        <a href="<?php echo BASE_URL; ?>/index.php#contacto">
            Contacto
        </a>

    </nav>


    <div class="acciones">

        <?php if (isset($_SESSION["user_id"])): ?>


            <span class="usuario-header">

                Hola,
                <?php
                echo htmlspecialchars(
                    $_SESSION["nombres"]
                );
                ?>

            </span>


            <a href="<?php echo BASE_URL; ?>/cuenta/pedidos.php">
                Mis pedidos
            </a>


            <a href="<?php echo BASE_URL; ?>/auth/logout.php">
                Cerrar sesión
            </a>


        <?php else: ?>


            <a href="<?php echo BASE_URL; ?>/auth/login.php">
                Iniciar sesión
            </a>


            <a href="<?php echo BASE_URL; ?>/auth/registro.php">
                Registrarse
            </a>


        <?php endif; ?>


        <a href="<?php echo BASE_URL; ?>/carrito.php">
            Carrito
        </a>

    </div>

</header>