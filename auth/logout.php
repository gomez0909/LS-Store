<?php

session_start();

unset($_SESSION["user_id"]);
unset($_SESSION["nombres"]);
unset($_SESSION["apellidos"]);
unset($_SESSION["correo"]);
unset($_SESSION["rol"]);

header("Location: ../index.php");
exit;