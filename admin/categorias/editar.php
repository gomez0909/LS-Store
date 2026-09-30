<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


$mensaje = "";


/* =========================================================
   VALIDAR ID
   ========================================================= */

$id_categoria = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id_categoria) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   CONSULTAR CATEGORÍA
   ========================================================= */

$sql_categoria = "
    SELECT
        id,
        nombre,
        estado

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
    $id_categoria
);


$stmt_categoria->execute();


$categoria =
    $stmt_categoria
        ->get_result()
        ->fetch_assoc();


if (!$categoria) {

    header("Location: index.php");
    exit;

}


/* =========================================================
   PROCESAR EDICIÓN
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nombre =
        trim($_POST["nombre"] ?? "");


    if ($nombre === "") {

        $mensaje =
            "Debes escribir el nombre de la categoría.";

    } else {


        /* =================================================
           COMPROBAR QUE NO EXISTA OTRA IGUAL
           ================================================= */

        $sql_existe = "
            SELECT id

            FROM categorias

            WHERE nombre = ?
            AND id != ?

            LIMIT 1
        ";


        $stmt_existe =
            $conexion->prepare(
                $sql_existe
            );


        $stmt_existe->bind_param(
            "si",
            $nombre,
            $id_categoria
        );


        $stmt_existe->execute();


        $existe =
            $stmt_existe
                ->get_result()
                ->fetch_assoc();


        if ($existe) {

            $mensaje =
                "Ya existe otra categoría con ese nombre.";

        } else {


            $sql_actualizar = "
                UPDATE categorias

                SET nombre = ?

                WHERE id = ?
            ";


            $stmt_actualizar =
                $conexion->prepare(
                    $sql_actualizar
                );


            $stmt_actualizar->bind_param(
                "si",
                $nombre,
                $id_categoria
            );


            $stmt_actualizar->execute();


            header(
                "Location: index.php?editado=correcto"
            );

            exit;

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
        Editar categoría | LS Store
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

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

                <p>
                    LS STORE
                </p>

                <h1>
                    Editar categoría
                </h1>

            </div>


            <a
                href="index.php"
                class="admin-btn-secundario"
            >
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
                class="admin-form"
            >


                <div class="admin-campo">


                    <label for="nombre">

                        Nombre

                    </label>


                    <input
                        type="text"
                        id="nombre"
                        name="nombre"

                        value="<?php
                            echo htmlspecialchars(
                                $_POST["nombre"]
                                ?? $categoria["nombre"]
                            );
                        ?>"

                        maxlength="100"

                        required
                    >


                </div>



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