<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


$mensaje = "";


// Obtener categorías activas
$sql_categorias = "
    SELECT id, nombre
    FROM categorias
    WHERE estado = 'activo'
    ORDER BY nombre ASC
";

$resultado_categorias =
    $conexion->query($sql_categorias);


// Procesar formulario
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


    // Stocks
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


    /* =========================
       VALIDACIONES GENERALES
       ========================= */

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
    } elseif (
        !isset($_FILES["imagen"]) ||
        $_FILES["imagen"]["error"] !== UPLOAD_ERR_OK
    ) {

        $mensaje =
            "Debes seleccionar una imagen válida.";
    } else {


        /* =========================
           VALIDAR CATEGORÍA
           ========================= */

        $sql_categoria = "
            SELECT id
            FROM categorias
            WHERE id = ?
            AND estado = 'activo'
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


            /* =========================
               VALIDAR IMAGEN
               ========================= */

            $archivo_temporal =
                $_FILES["imagen"]["tmp_name"];

            $tamano =
                $_FILES["imagen"]["size"];


            // Máximo 5 MB
            if ($tamano > 5 * 1024 * 1024) {

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


                    /* =========================
                       CREAR NOMBRE ÚNICO
                       ========================= */

                    $extension =
                        $tipos_permitidos[$mime];


                    $nombre_imagen =
                        "producto_" .
                        bin2hex(
                            random_bytes(8)
                        ) .
                        "." .
                        $extension;


                    $ruta_destino =
                        __DIR__ .
                        "/../../assets/img/" .
                        $nombre_imagen;



                    /* =========================
                       GUARDAR IMAGEN
                       ========================= */

                    if (
                        !move_uploaded_file(
                            $archivo_temporal,
                            $ruta_destino
                        )
                    ) {

                        $mensaje =
                            "No fue posible guardar la imagen.";
                    } else {


                        /* =========================
                           CREAR PRODUCTO
                           ========================= */

                        mysqli_report(
                            MYSQLI_REPORT_ERROR |
                                MYSQLI_REPORT_STRICT
                        );


                        $conexion
                            ->begin_transaction();


                        try {


                            $estado =
                                "activo";


                            $sql_producto = "
                                INSERT INTO productos
                                (
                                    categoria_id,
                                    nombre,
                                    descripcion,
                                    precio,
                                    imagen,
                                    color,
                                    estado
                                )
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                            ";


                            $stmt_producto =
                                $conexion->prepare(
                                    $sql_producto
                                );


                            $stmt_producto->bind_param(
                                "issdsss",
                                $categoria_id,
                                $nombre,
                                $descripcion,
                                $precio,
                                $nombre_imagen,
                                $color,
                                $estado
                            );


                            $stmt_producto->execute();


                            $producto_id =
                                $conexion->insert_id;



                            /* =========================
                               CREAR TALLAS
                               ========================= */

                            $sql_talla = "
                                INSERT INTO producto_tallas
                                (
                                    producto_id,
                                    talla,
                                    stock
                                )
                                VALUES (?, ?, ?)
                            ";


                            $stmt_talla =
                                $conexion->prepare(
                                    $sql_talla
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


                                $stmt_talla->bind_param(
                                    "isi",
                                    $producto_id,
                                    $talla,
                                    $stock
                                );


                                $stmt_talla->execute();
                            }


                            $conexion->commit();


                            header(
                                "Location: index.php?creado=correcto"
                            );

                            exit;
                        } catch (Throwable $error) {


                            $conexion->rollback();


                            // Si falla la BD,
                            // eliminamos la imagen subida
                            if (
                                file_exists(
                                    $ruta_destino
                                )
                            ) {

                                unlink(
                                    $ruta_destino
                                );
                            }


                            error_log(
                                "Error creando producto: " .
                                    $error->getMessage()
                            );


                            $mensaje =
                                "No fue posible crear el producto.";
                        }
                    }
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
        content="width=device-width, initial-scale=1.0">

    <title>Nuevo producto | LS Store</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css">

</head>


<body>


    <div class="admin-layout">


        <?php

        require_once __DIR__ .
            "/../includes/sidebar.php";

        ?>


        <main class="admin-main">


            <div class="admin-cabecera">

                <div>

                    <p>LS STORE</p>

                    <h1>Nuevo producto</h1>

                </div>


                <a
                    href="index.php"
                    class="admin-btn-secundario">
                    Volver
                </a>

            </div>



            <?php if ($mensaje !== ""): ?>

                <div class="admin-mensaje">

                    <?php
                    echo htmlspecialchars(
                        $mensaje
                    );
                    ?>

                </div>

            <?php endif; ?>



            <section class="admin-form-panel">


                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="admin-form">


                    <div class="admin-form-grid">


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
                                            $_POST["nombre"] ?? ""
                                        );
                                        ?>"

                                required>

                        </div>



                        <div class="admin-campo">

                            <label for="categoria_id">
                                Categoría
                            </label>


                            <select
                                id="categoria_id"
                                name="categoria_id"
                                required>


                                <option value="">
                                    Selecciona una categoría
                                </option>


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
                                            isset(
                                                $_POST["categoria_id"]
                                            ) &&
                                            (int)
                                            $_POST["categoria_id"] ===
                                            (int)
                                            $categoria["id"]
                                        ) {

                                            echo "selected";
                                        }

                                        ?>>

                                        <?php
                                        echo htmlspecialchars(
                                            $categoria["nombre"]
                                        );
                                        ?>

                                    </option>


                                <?php endwhile; ?>


                            </select>

                        </div>



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
                                            $_POST["precio"] ?? ""
                                        );
                                        ?>"

                                required>

                        </div>



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
                                            $_POST["color"] ?? ""
                                        );
                                        ?>"

                                required>

                        </div>


                    </div>



                    <div class="admin-campo">

                        <label for="descripcion">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="5"
                            required><?php
                                        echo htmlspecialchars(
                                            $_POST["descripcion"] ?? ""
                                        );
                                        ?></textarea>

                    </div>



                    <div class="admin-campo">

                        <label for="imagen">
                            Imagen principal
                        </label>

                        <input
                            type="file"
                            id="imagen"
                            name="imagen"

                            accept=".jpg,.jpeg,.png,.webp"

                            required>

                        <small>
                            JPG, PNG o WEBP. Máximo 5 MB.
                        </small>

                    </div>



                    <div class="admin-stock">


                        <h2>
                            Inventario por talla
                        </h2>


                        <p>
                            Ingresa cuántas unidades
                            existen de cada talla.
                        </p>



                        <div class="admin-stock-grid">


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
                                                    ?? "0"
                                            );
                                            ?>"

                                    required>

                            </div>



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
                                                    ?? "0"
                                            );
                                            ?>"

                                    required>

                            </div>



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
                                                    ?? "0"
                                            );
                                            ?>"

                                    required>

                            </div>



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
                                                    ?? "0"
                                            );
                                            ?>"

                                    required>

                            </div>


                        </div>


                    </div>



                    <button
                        type="submit"
                        class="admin-btn-principal">
                        Crear producto
                    </button>


                </form>


            </section>


        </main>


    </div>


</body>

</html>