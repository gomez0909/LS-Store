<?php

session_start();

require_once "../config/conexion.php";

$mensaje = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombres = trim($_POST["nombres"] ?? "");
    $apellidos = trim($_POST["apellidos"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmar_password = $_POST["confirmar_password"] ?? "";


    if (
        $nombres === "" ||
        $apellidos === "" ||
        $correo === "" ||
        $password === "" ||
        $confirmar_password === ""
    ) {

        $mensaje = "Completa todos los campos obligatorios.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } elseif (strlen($password) < 8) {

        $mensaje = "La contraseña debe tener mínimo 8 caracteres.";

    } elseif ($password !== $confirmar_password) {

        $mensaje = "Las contraseñas no coinciden.";

    } else {

        $sql_correo = "
            SELECT id
            FROM usuarios
            WHERE correo = ?
            LIMIT 1
        ";

        $stmt_correo = $conexion->prepare($sql_correo);

        $stmt_correo->bind_param(
            "s",
            $correo
        );

        $stmt_correo->execute();

        $resultado_correo = $stmt_correo->get_result();


        if ($resultado_correo->num_rows > 0) {

            $mensaje = "Ya existe una cuenta con este correo.";

        } else {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $rol = "cliente";
            $estado = "activo";


            $sql_usuario = "
                INSERT INTO usuarios
                (
                    nombres,
                    apellidos,
                    correo,
                    password,
                    telefono,
                    rol,
                    estado
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";


            $stmt_usuario = $conexion->prepare($sql_usuario);

            $stmt_usuario->bind_param(
                "sssssss",
                $nombres,
                $apellidos,
                $correo,
                $password_hash,
                $telefono,
                $rol,
                $estado
            );


            if ($stmt_usuario->execute()) {

                header("Location: login.php?registro=correcto");
                exit;

            } else {

                $mensaje = "No fue posible crear la cuenta.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear cuenta | LS Store</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <main class="auth">

        <section class="auth-contenedor">

            <a href="../index.php" class="auth-logo">
                LS STORE
            </a>

            <h1>Crear cuenta</h1>

            <p class="auth-texto">
                Regístrate para realizar tus compras.
            </p>


            <?php if ($mensaje !== ""): ?>

                <div class="auth-mensaje">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>

            <?php endif; ?>


            <form method="POST" class="auth-form">

                <div class="campo">
                    <label for="nombres">Nombres</label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        required
                    >
                </div>


                <div class="campo">
                    <label for="apellidos">Apellidos</label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        required
                    >
                </div>


                <div class="campo">
                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        required
                    >
                </div>


                <div class="campo">
                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                    >
                </div>


                <div class="campo">
                    <label for="password">Contraseña</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        required
                    >
                </div>


                <div class="campo">
                    <label for="confirmar_password">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        minlength="8"
                        required
                    >
                </div>


                <button type="submit" class="btn-auth">
                    Crear cuenta
                </button>

            </form>


            <p class="auth-cambio">
                ¿Ya tienes una cuenta?

                <a href="auth/login.php">
                    Iniciar sesión
                </a>
            </p>

        </section>

    </main>

</body>

</html>