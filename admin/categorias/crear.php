<?php

require_once __DIR__ . "/../includes/auth_admin.php";
require_once __DIR__ . "/../../config/conexion.php";


$mensaje = "";


/* =========================================================
   PROCESAR FORMULARIO
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nombre =
        trim($_POST["nombre"] ?? "");


    if ($nombre === "") {

        $mensaje =
            "Debes escribir el nombre de la categoría.";

    } else {


        /* =================================================
           COMPROBAR DUPLICADO
           ================================================= */

        $sql_existe = "
            SELECT id

            FROM categorias

            WHERE nombre = ?

            LIMIT 1
        ";


        $stmt_existe =
            $conexion->prepare(
                $sql_existe
            );


        $stmt_existe->bind_param(
            "s",
            $nombre
        );


        $stmt_existe->execute();


        $existe =
            $stmt_existe
                ->get_result()
                ->fetch_assoc();


        if ($existe) {

            $mensaje =
                "Ya existe una categoría con ese nombre.";

        } else {


            /* =================================================
               INSERTAR
               ================================================= */

            $estado =
                "activo";


            $sql_insertar = "
                INSERT INTO categorias
                (
                    nombre,
                    estado
                )

                VALUES (?, ?)
            ";


            $stmt_insertar =
                $conexion->prepare(
                    $sql_insertar
                );


            $stmt_insertar->bind_param(
                "ss",
                $nombre,
                $estado
            );


            $stmt_insertar->execute();


            header(
                "Location: index.php?creado=correcto"
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
        Nueva categoría | LS Store
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
                    Nueva categoría
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

                        Nombre de la categoría

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

                        placeholder="Ej: Gorras"

                        maxlength="100"

                        required
                    >


                </div>



                <button
                    type="submit"
                    class="admin-btn-principal"
                >
                    Crear categoría
                </button>


            </form>


        </section>


    </main>


</div>


</body>

</html>