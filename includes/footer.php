<?php

require_once __DIR__ . "/../config/app.php";

?>

<footer id="contacto">

    <div class="footer-contenido">


        <div class="footer-marca">

            <h2>LS STORE</h2>

            <p>
                Moda urbana para quienes crean su propio estilo.
            </p>

        </div>


        <div class="footer-seccion">

            <h3>Tienda</h3>

            <a href="<?php echo BASE_URL; ?>/index.php#productos">
                Productos
            </a>

            <a href="#">
                Camisetas
            </a>

            <a href="#">
                Hoodies
            </a>

            <a href="#">
                Pantalones
            </a>

        </div>


        <div class="footer-seccion">

            <h3>Ayuda</h3>

            <a href="<?php echo BASE_URL; ?>/index.php#contacto">
                Contacto
            </a>

            <a href="#">
                Envíos
            </a>

            <a href="#">
                Cambios y devoluciones
            </a>

        </div>


        <div class="footer-seccion">

            <h3>Síguenos</h3>

            <a href="#">
                Instagram
            </a>

            <a href="#">
                TikTok
            </a>

        </div>

    </div>


    <div class="footer-inferior">

        <p>
            &copy;
            <?php echo date("Y"); ?>
            LS Store. Todos los derechos reservados.
        </p>

    </div>

</footer>