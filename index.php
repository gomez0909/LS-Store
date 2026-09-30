<?php

session_start();

require_once "config/conexion.php";

$sql_productos = "
    SELECT
        id,
        nombre,
        precio,
        imagen
    FROM productos
    WHERE estado = 'activo'
    ORDER BY id ASC
    LIMIT 4
";

$resultado_productos = $conexion->query($sql_productos);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LS store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php

    require_once __DIR__ . "/includes/header.php";

    ?>

    <main>

        <section class="hero">

            <div class="hero-contenido">

                <p class="hero-etiqueta">Nueva colección</p>

                <h1>Define tu estilo</h1>

                <p>
                    Ropa con estilo, ropa urbana, diseñada para destacar
                    Descubre nuestra nueva colección, en LS Store
                </p>

                <a href="#" class="btn-comprar">
                    Comprar ahora
                </a>

            </div>

        </section>

        <section class="productos-destacados" id="productos">

            <div class="titulo-seccion">

                <p>LS STORE</p>

                <h2>Productos destacados</h2>


                <a href="productos.php" class="ver-todos-productos">
                    Ver todos los productos
                </a>

            </div>


            <div class="productos-grid">


                <?php while ($producto = $resultado_productos->fetch_assoc()): ?>


                    <article class="producto">


                        <a href="producto.php?id=<?php echo (int) $producto["id"]; ?>">

                            <img
                                src="assets/img/<?php echo htmlspecialchars($producto["imagen"]); ?>"
                                alt="<?php echo htmlspecialchars($producto["nombre"]); ?>">

                        </a>


                        <div class="producto-info">


                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );
                                ?>

                            </h3>


                            <p class="precio">

                                $<?php
                                    echo number_format(
                                        $producto["precio"],
                                        0,
                                        ",",
                                        "."
                                    );
                                    ?>

                            </p>


                            <a
                                href="producto.php?id=<?php echo (int) $producto["id"]; ?>"
                                class="btn-producto">
                                Ver producto
                            </a>


                        </div>


                    </article>


                <?php endwhile; ?>


            </div>

        </section>

        <section class="categorias">

            <div class="titulo-seccion">

                <p>ENCUENTRA TU ESTILO</p>
                <h2>Comprar por categoría</h2>

            </div>

            <div class="categorias-grid">

                <a href="#" class="categoria">

                    <img src="assets/img/categoria-camisetas.png" alt="Camisetas">

                    <div class="categoria-nombre">

                        <h3>Camisetas</h3>

                    </div>

                </a>

                <a href="#" class="categoria">

                    <img src="assets/img/categoria-hoodies.png" alt="Hoodies">

                    <div class="categoria-nombre">

                        <h3>Hoodies</h3>

                    </div>

                </a>

                <a href="#" class="categoria">

                    <img src="assets/img/categoria-jeans.png" alt="Jeans">

                    <div class="categoria-nombre">

                        <h3>Jeans</h3>

                    </div>

                </a>

            </div>

        </section>

    </main>

    <?php

    require_once __DIR__ . "/includes/footer.php";

    ?>

</body>

</html>