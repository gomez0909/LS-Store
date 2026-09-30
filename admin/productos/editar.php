<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


$mensaje = "";


/* =========================================================
   VALIDAR ID DEL PRODUCTO
   ========================================================= */

$id_producto = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_producto) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   BUSCAR PRODUCTO
   ========================================================= */

$sql_producto = "
    SELECT
        id,
        categoria_id,
        nombre,
        descripcion,
        precio,
        imagen,
        color,
        estado
    FROM productos
    WHERE id = ?
    LIMIT 1
";


$stmt_producto = $conexion->prepare($sql_producto);

$stmt_producto->bind_param(
    "i",
    $id_producto
);

$stmt_producto->execute();

$resultado_producto = $stmt_producto->get_result();

$producto = $resultado_producto->fetch_assoc();


if (!$producto) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   OBTENER STOCK ACTUAL POR TALLA
   ========================================================= */

$sql_tallas = "
    SELECT
        talla,
        stock
    FROM producto_tallas
    WHERE producto_id = ?
";


$stmt_tallas = $conexion->prepare($sql_tallas);

$stmt_tallas->bind_param(
    "i",
    $id_producto
);

$stmt_tallas->execute();

$resultado_tallas = $stmt_tallas->get_result();


$stocks = [
    "S" => 0,
    "M" => 0,
    "L" => 0,
    "XL" => 0
];


while ($fila = $resultado_tallas->fetch_assoc()) {

    $stocks[$fila["talla"]] =
        (int) $fila["stock"];

}


/* =========================================================
   OBTENER CATEGORÍAS
   ========================================================= */

$sql_categorias = "
    SELECT
        id,
        nombre
    FROM categorias
    WHERE estado = 'activo'
    OR id = ?
    ORDER BY nombre ASC
";


$stmt_categorias =
    $conexion->prepare($sql_categorias);


$stmt_categorias->bind_param(
    "i",
    $producto["categoria_id"]
);


$stmt_categorias->execute();

$resultado_categorias =
    $stmt_categorias->get_result();


/* =========================================================
   PROCESAR FORMULARIO
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nombre =
        trim($_POST["nombre"] ?? "");


    $categoria_id =
        filter_input(
            INPUT_POST,
            "categoria_id",
            FILTER_VALIDATE_INT
        );


    $descripcion =
        trim($_POST["descripcion"] ?? "");


    $precio =
        filter_input(
            INPUT_POST,
            "precio",
            FILTER_VALIDATE_FLOAT
        );


    $color =
        trim($_POST["color"] ?? "");



    /* ==========================
       STOCK
       ========================== */

    $stock_s =
        filter_input(
            INPUT_POST,
            "stock_s",
            FILTER_VALIDATE_INT
        );


    $stock_m =
        filter_input(
            INPUT_POST,
            "stock_m",
            FILTER_VALIDATE_INT
        );


    $stock_l =
        filter_input(
            INPUT_POST,
            "stock_l",
            FILTER_VALIDATE_INT
        );


    $stock_xl =
        filter_input(
            INPUT_POST,
            "stock_xl",
            FILTER_VALIDATE_INT
        );



    /* =====================================================
       VALIDACIONES
       ===================================================== */

    if (
        $nombre === "" ||
        !$categoria_id ||
        $descripcion === "" ||
        $precio === false ||
        $precio <= 0 ||
        $color === ""
    ) {

        $mensaje =
            "Completa correctamente todos los datos del producto.";

    } elseif (
        $stock_s === false ||
        $stock_m === false ||
        $stock_l === false ||
        $stock_xl === false ||
        $stock_s < 0 ||
        $stock_m < 0 ||
        $stock_l < 0 ||
        $stock_xl < 0
    ) {

        $mensaje =
            "El stock de las tallas no puede ser negativo.";

    } else {



        /* =================================================
           VALIDAR CATEGORÍA
           ================================================= */

        $sql_categoria = "
            SELECT id
            FROM categorias
            WHERE id = ?
            LIMIT 1
        ";


        $stmt_categoria =
            $conexion->prepare(
                $sql_categoria
            );


        $stmt_categoria->bind_param(
            "i",
            $categoria_id
        );


        $stmt_categoria->execute();


        $categoria =
            $stmt_categoria
                ->get_result()
                ->fetch_assoc();


        if (!$categoria) {

            $mensaje =
                "La categoría seleccionada no es válida.";

        } else {



            /* =================================================
               IMAGEN
               ================================================= */

            $nombre_imagen =
                $producto["imagen"];


            $ruta_imagen_nueva =
                null;


            $imagen_anterior =
                $producto["imagen"];



            /*
                Si no sube una nueva imagen,
                conservamos la anterior.
            */

            if (
                isset($_FILES["imagen"]) &&
                $_FILES["imagen"]["error"]
                !== UPLOAD_ERR_NO_FILE
            ) {


                if (
                    $_FILES["imagen"]["error"]
                    !== UPLOAD_ERR_OK
                ) {

                    $mensaje =
                        "Ocurrió un error al subir la imagen.";

                } else {


                    $archivo_temporal =
                        $_FILES["imagen"]["tmp_name"];


                    $tamano =
                        $_FILES["imagen"]["size"];


                    /* Máximo 5 MB */

                    if (
                        $tamano >
                        5 * 1024 * 1024
                    ) {

                        $mensaje =
                            "La imagen no puede superar los 5 MB.";

                    } else {


                        $finfo =
                            finfo_open(
                                FILEINFO_MIME_TYPE
                            );


                        $mime =
                            finfo_file(
                                $finfo,
                                $archivo_temporal
                            );


                        finfo_close($finfo);



                        $tipos_permitidos = [

                            "image/jpeg" => "jpg",

                            "image/png" => "png",

                            "image/webp" => "webp"

                        ];



                        if (
                            !isset(
                                $tipos_permitidos[$mime]
                            )
                        ) {

                            $mensaje =
                                "La imagen debe ser JPG, PNG o WEBP.";

                        } else {


                            $extension =
                                $tipos_permitidos[$mime];


                            $nombre_imagen =
                                "producto_" .
                                bin2hex(
                                    random_bytes(8)
                                ) .
                                "." .
                                $extension;



                            $ruta_imagen_nueva =
                                __DIR__ .
                                "/../../assets/img/" .
                                $nombre_imagen;



                            if (
                                !move_uploaded_file(
                                    $archivo_temporal,
                                    $ruta_imagen_nueva
                                )
                            ) {

                                $mensaje =
                                    "No fue posible guardar la nueva imagen.";

                            }

                        }

                    }

                }

            }



            /* =================================================
               ACTUALIZAR PRODUCTO
               ================================================= */

            if ($mensaje === "") {


                mysqli_report(
                    MYSQLI_REPORT_ERROR |
                    MYSQLI_REPORT_STRICT
                );


                $conexion->begin_transaction();


                try {


                    /* =========================================
                       ACTUALIZAR DATOS GENERALES
                       ========================================= */

                    $sql_actualizar = "
                        UPDATE productos

                        SET
                            categoria_id = ?,
                            nombre = ?,
                            descripcion = ?,
                            precio = ?,
                            imagen = ?,
                            color = ?

                        WHERE id = ?
                    ";


                    $stmt_actualizar =
                        $conexion->prepare(
                            $sql_actualizar
                        );


                    $stmt_actualizar->bind_param(
                        "issdssi",
                        $categoria_id,
                        $nombre,
                        $descripcion,
                        $precio,
                        $nombre_imagen,
                        $color,
                        $id_producto
                    );


                    $stmt_actualizar->execute();



                    /* =========================================
                       ACTUALIZAR STOCK
                       ========================================= */

                    $sql_stock = "
                        INSERT INTO producto_tallas
                        (
                            producto_id,
                            talla,
                            stock
                        )

                        VALUES (?, ?, ?)

                        ON DUPLICATE KEY UPDATE

                            stock = VALUES(stock)
                    ";


                    $stmt_stock =
                        $conexion->prepare(
                            $sql_stock
                        );


                    $tallas = [

                        "S" => $stock_s,

                        "M" => $stock_m,

                        "L" => $stock_l,

                        "XL" => $stock_xl

                    ];



                    foreach (
                        $tallas
                        as $talla => $stock
                    ) {


                        $stmt_stock->bind_param(
                            "isi",
                            $id_producto,
                            $talla,
                            $stock
                        );


                        $stmt_stock->execute();

                    }



                    /* =========================================
                       CONFIRMAR TRANSACCIÓN
                       ========================================= */

                    $conexion->commit();



                    /* =========================================
                       ELIMINAR IMAGEN ANTERIOR
                       SOLO SI SE SUBIÓ UNA NUEVA
                       ========================================= */

                    if (
                        $ruta_imagen_nueva !== null &&
                        !empty($imagen_anterior) &&
                        $imagen_anterior !== $nombre_imagen
                    ) {


                        /*
                            Comprobamos que ningún otro
                            producto esté usando la misma
                            imagen antes de eliminarla.
                        */

                        $sql_uso_imagen = "
                            SELECT COUNT(*) AS total

                            FROM productos

                            WHERE imagen = ?
                            AND id != ?
                        ";


                        $stmt_uso_imagen =
                            $conexion->prepare(
                                $sql_uso_imagen
                            );


                        $stmt_uso_imagen->bind_param(
                            "si",
                            $imagen_anterior,
                            $id_producto
                        );


                        $stmt_uso_imagen->execute();


                        $uso_imagen =
                            $stmt_uso_imagen
                                ->get_result()
                                ->fetch_assoc();


                        if (
                            (int) $uso_imagen["total"] === 0
                        ) {


                            $ruta_anterior =
                                __DIR__ .
                                "/../../assets/img/" .
                                $imagen_anterior;


                            if (
                                file_exists(
                                    $ruta_anterior
                                )
                            ) {

                                unlink(
                                    $ruta_anterior
                                );

                            }

                        }

                    }



                    header(
                        "Location: index.php?editado=correcto"
                    );

                    exit;



                } catch (Throwable $error) {


                    $conexion->rollback();



                    /*
                        Si subimos una nueva imagen
                        pero falló la BD,
                        eliminamos esa nueva imagen.
                    */

                    if (
                        $ruta_imagen_nueva !== null &&
                        file_exists(
                            $ruta_imagen_nueva
                        )
                    ) {

                        unlink(
                            $ruta_imagen_nueva
                        );

                    }



                    error_log(
                        "Error editando producto: " .
                        $error->getMessage()
                    );


                    $mensaje =
                        "No fue posible actualizar el producto.";

                }

            }

        }

    }

}

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
        Editar producto | LS Store
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- SIDEBAR -->

    <?php

        require_once __DIR__ .
            "/../includes/sidebar.php";

    ?>



    <!-- CONTENIDO -->

    <main class="admin-main">


        <!-- CABECERA -->

        <div class="admin-cabecera">


            <div>

                <p>
                    LS STORE
                </p>

                <h1>
                    Editar producto
                </h1>

            </div>


            <a
                href="index.php"
                class="admin-btn-secundario"
            >
                Volver
            </a>


        </div>



        <!-- MENSAJES -->

        <?php if ($mensaje !== ""): ?>


            <div class="admin-mensaje">

                <?php
                    echo htmlspecialchars(
                        $mensaje
                    );
                ?>

            </div>


        <?php endif; ?>



        <!-- FORMULARIO -->

        <section class="admin-form-panel">


            <form
                method="POST"
                enctype="multipart/form-data"
                class="admin-form"
            >



                <!-- DATOS GENERALES -->

                <div class="admin-form-grid">


                    <!-- NOMBRE -->

                    <div class="admin-campo">

                        <label for="nombre">
                            Nombre del producto
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"

                            value="<?php
                                echo htmlspecialchars(
                                    $_POST["nombre"]
                                    ?? $producto["nombre"]
                                );
                            ?>"

                            required
                        >

                    </div>



                    <!-- CATEGORÍA -->

                    <div class="admin-campo">

                        <label for="categoria_id">
                            Categoría
                        </label>


                        <select
                            id="categoria_id"
                            name="categoria_id"
                            required
                        >


                            <?php


                            $categoria_actual =

                                isset(
                                    $_POST["categoria_id"]
                                )

                                    ? (int)
                                        $_POST["categoria_id"]

                                    : (int)
                                        $producto["categoria_id"];


                            ?>


                            <?php while (
                                $categoria =
                                    $resultado_categorias
                                        ->fetch_assoc()
                            ): ?>


                                <option
                                    value="<?php
                                        echo (int)
                                            $categoria["id"];
                                    ?>"

                                    <?php

                                    if (
                                        $categoria_actual
                                        ===
                                        (int)
                                        $categoria["id"]
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            $categoria["nombre"]
                                        );
                                    ?>

                                </option>


                            <?php endwhile; ?>


                        </select>

                    </div>



                    <!-- PRECIO -->

                    <div class="admin-campo">

                        <label for="precio">
                            Precio
                        </label>

                        <input
                            type="number"
                            id="precio"
                            name="precio"

                            min="1"
                            step="0.01"

                            value="<?php
                                echo htmlspecialchars(
                                    $_POST["precio"]
                                    ?? $producto["precio"]
                                );
                            ?>"

                            required
                        >

                    </div>



                    <!-- COLOR -->

                    <div class="admin-campo">

                        <label for="color">
                            Color
                        </label>

                        <input
                            type="text"
                            id="color"
                            name="color"

                            value="<?php
                                echo htmlspecialchars(
                                    $_POST["color"]
                                    ?? $producto["color"]
                                );
                            ?>"

                            required
                        >

                    </div>


                </div>



                <!-- DESCRIPCIÓN -->

                <div class="admin-campo">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="5"
                        required
                    ><?php
                        echo htmlspecialchars(
                            $_POST["descripcion"]
                            ?? $producto["descripcion"]
                        );
                    ?></textarea>

                </div>



                <!-- IMAGEN ACTUAL -->

                <div class="admin-imagen-actual">


                    <p>
                        Imagen actual
                    </p>


                    <?php if (
                        !empty($producto["imagen"])
                    ): ?>


                        <img
                            src="../../assets/img/<?php
                                echo htmlspecialchars(
                                    $producto["imagen"]
                                );
                            ?>"

                            alt="<?php
                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );
                            ?>"
                        >


                    <?php else: ?>


                        <span>
                            Este producto no tiene imagen.
                        </span>


                    <?php endif; ?>


                </div>



                <!-- NUEVA IMAGEN -->

                <div class="admin-campo">

                    <label for="imagen">
                        Cambiar imagen
                    </label>

                    <input
                        type="file"
                        id="imagen"
                        name="imagen"

                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Déjalo vacío para conservar la imagen actual.
                        JPG, PNG o WEBP. Máximo 5 MB.
                    </small>

                </div>



                <!-- STOCK -->

                <div class="admin-stock">


                    <h2>
                        Inventario por talla
                    </h2>


                    <p>
                        Modifica las unidades disponibles
                        de cada talla.
                    </p>



                    <div class="admin-stock-grid">


                        <!-- S -->

                        <div class="admin-campo">

                            <label for="stock_s">
                                Talla S
                            </label>

                            <input
                                type="number"
                                id="stock_s"
                                name="stock_s"

                                min="0"

                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["stock_s"]
                                        ?? $stocks["S"]
                                    );
                                ?>"

                                required
                            >

                        </div>



                        <!-- M -->

                        <div class="admin-campo">

                            <label for="stock_m">
                                Talla M
                            </label>

                            <input
                                type="number"
                                id="stock_m"
                                name="stock_m"

                                min="0"

                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["stock_m"]
                                        ?? $stocks["M"]
                                    );
                                ?>"

                                required
                            >

                        </div>



                        <!-- L -->

                        <div class="admin-campo">

                            <label for="stock_l">
                                Talla L
                            </label>

                            <input
                                type="number"
                                id="stock_l"
                                name="stock_l"

                                min="0"

                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["stock_l"]
                                        ?? $stocks["L"]
                                    );
                                ?>"

                                required
                            >

                        </div>



                        <!-- XL -->

                        <div class="admin-campo">

                            <label for="stock_xl">
                                Talla XL
                            </label>

                            <input
                                type="number"
                                id="stock_xl"
                                name="stock_xl"

                                min="0"

                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["stock_xl"]
                                        ?? $stocks["XL"]
                                    );
                                ?>"

                                required
                            >

                        </div>


                    </div>


                </div>



                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="admin-btn-principal"
                >
                    Guardar cambios
                </button>


            </form>


        </section>


    </main>


</div>


</body>

</html>