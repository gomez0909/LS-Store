<?php

session_start();

require_once "../config/conexion.php";


$mensaje = "";


// Si viene desde checkout, conservamos esta información
$redirect = $_GET["redirect"] ?? "";


// Mensaje después de registrarse
if (
    isset($_GET["registro"]) &&
    $_GET["registro"] === "correcto"
) {

    $mensaje = "Cuenta creada correctamente. Ahora puedes iniciar sesión.";

}


// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $password = $_POST["password"] ?? "";

    // Recuperamos el destino enviado por el formulario
    $redirect = $_POST["redirect"] ?? "";


    if ($correo === "" || $password === "") {

        $mensaje = "Completa todos los campos.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } else {


        $sql = "
            SELECT
                id,
                nombres,
                apellidos,
                correo,
                password,
                rol,
                estado

            FROM usuarios

            WHERE correo = ?

            LIMIT 1
        ";


        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "s",
            $correo
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        $usuario = $resultado->fetch_assoc();


        if (!$usuario) {

            $mensaje = "Correo o contraseña incorrectos.";

        } elseif ($usuario["estado"] !== "activo") {

            $mensaje = "Esta cuenta se encuentra inactiva.";

        } elseif (!password_verify(
            $password,
            $usuario["password"]
        )) {

            $mensaje = "Correo o contraseña incorrectos.";

        } else {


            // Cambiamos el ID de sesión al iniciar sesión
            session_regenerate_id(true);


            // Guardamos datos del usuario
            $_SESSION["user_id"] = $usuario["id"];

            $_SESSION["nombres"] = $usuario["nombres"];

            $_SESSION["apellidos"] = $usuario["apellidos"];

            $_SESSION["correo"] = $usuario["correo"];

            $_SESSION["rol"] = $usuario["rol"];


            // Administrador
            if ($usuario["rol"] === "administrador") {

                header("Location: ../admin/index.php");
                exit;

            }


            // Si venía desde finalizar compra
            if ($redirect === "checkout") {

                header("Location: ../checkout.php");
                exit;

            }


            // Login normal
            header("Location: ../index.php");
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

    <title>Iniciar sesión | LS Store</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<main class="auth">


    <section class="auth-contenedor">


        <a
            href="../index.php"
            class="auth-logo"
        >
            LS STORE
        </a>


        <h1>Iniciar sesión</h1>


        <p class="auth-texto">
            Accede a tu cuenta para continuar.
        </p>


        <?php if ($mensaje !== ""): ?>

            <div class="auth-mensaje">

                <?php
                    echo htmlspecialchars($mensaje);
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="auth-form"
        >


            <!--
                Conserva checkout como destino
                si el usuario llegó desde el carrito.
            -->
            <input
                type="hidden"
                name="redirect"
                value="<?php echo htmlspecialchars($redirect); ?>"
            >


            <div class="campo">

                <label for="correo">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"

                    value="<?php
                        echo htmlspecialchars(
                            $_POST["correo"] ?? ""
                        );
                    ?>"

                    required
                >

            </div>


            <div class="campo">

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-auth"
            >
                Iniciar sesión
            </button>


        </form>


        <p class="auth-cambio">

            ¿No tienes una cuenta?

            <a href="registro.php">
                Registrarse
            </a>

        </p>


    </section>


</main>


</body>

</html>