<?php
session_start();
require_once "conexion.php"; // Carga $mysqli

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_POST["nombre"]) && !empty($_POST["correo"]) && !empty($_POST["password"])) {

        $nombre = trim($_POST["nombre"]);
        $correo = trim($_POST["correo"]);
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $rol = "user"; // Todos los que se registran son user
        $saldo = 0;

        // Verificar si el correo ya existe
        $sql = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "Este correo ya está registrado.";
        } else {
            // Insertar usuario
            $sql = "INSERT INTO usuarios (nombre, correo, password, saldo, rol) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssds", $nombre, $correo, $password, $saldo, $rol);

            if ($stmt->execute()) {
                $mensaje = "Registro exitoso. Ya puedes iniciar sesión.";
            } else {
                $mensaje = "Error al registrar usuario.";
            }
        }

        $stmt->close();
        $mysqli->close();

    } else {
        $mensaje = "Por favor complete todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h2>Registro de Usuario</h2>

    <?php if ($mensaje != ""): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required><br><br>
        <input type="email" name="correo" placeholder="Correo" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>
        <button type="submit">Registrarme</button>
    </form>

    <br>
    <a href="index.php">Volver al login</a>
</body>
</html>
