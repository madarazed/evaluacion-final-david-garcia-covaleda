<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    if ($correo === "" || $password === "") {
        $mensaje = "Por favor completa todos los campos.";
    } else {

        // Consultar usuario
        $stmt = $mysqli->prepare("SELECT id, nombre, correo, password, rol FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verificar contraseña
            if (password_verify($password, $user["password"])) {

                // Guardar sesión
                $_SESSION["usuario_id"] = $user["id"];
                $_SESSION["usuario_nombre"] = $user["nombre"];
                $_SESSION["rol"] = $user["rol"];

                // Redirección según rol
                if ($user["rol"] === "admin") {
                    header("Location: dashboard_admin.php");
                } else {
                    header("Location: dashboard_user.php");
                }
                exit;

            } else {
                $mensaje = "Correo o contraseña incorrectos.";
            }

        } else {
            $mensaje = "Correo o contraseña incorrectos.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Nequi Simple</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-body">

                    <h3 class="text-center mb-3">Iniciar sesión</h3>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-danger"><?= $mensaje ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Correo:</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Contraseña:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Entrar</button>

                        <div class="text-center mt-3">
                            <a href="registro.php">Crear cuenta</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
