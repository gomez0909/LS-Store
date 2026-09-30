<?php

require_once __DIR__ . "/../../config/app.php";

?>

<aside class="admin-sidebar">

    <div class="admin-logo">

        <a href="<?php echo BASE_URL; ?>/admin/index.php">
            LS STORE
        </a>

        <span>ADMIN</span>

    </div>


    <nav class="admin-menu">

        <a href="<?php echo BASE_URL; ?>/admin/index.php">
            Dashboard
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/productos/index.php">
            Productos
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/pedidos/index.php">
            Pedidos
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/categorias/index.php">
            Categorías
        </a>

        <a href="<?php echo BASE_URL; ?>/index.php">
            Ver tienda
        </a>

    </nav>


    <div class="admin-sidebar-footer">

        <p>
            <?php
            echo htmlspecialchars(
                $_SESSION["nombres"]
            );
            ?>
        </p>

        <a href="<?php echo BASE_URL; ?>/auth/logout.php">
            Cerrar sesión
        </a>

    </div>

</aside>