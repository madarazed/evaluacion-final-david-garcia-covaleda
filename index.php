<?php
// index.php - Login
session_start();
require_once 'conexion.php'; // tu archivo de conexión

// Si ya está autenticado, redirigir según rol
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin_home.php');
        exit;
    } else {
        header('Location: dashboard_user.php');
        exit;
    }
}

// Generar token CSRF simple para el formulario (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
}

// Procesar formulario (POST)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = 'Token inválido. Recarga la página e intenta de nuevo.';
    } else {
        // Inputs
        $input_usuario = trim($_POST['usuario'] ?? '');
        $input_password = $_POST['password'] ?? '';

        if ($input_usuario === '' || $input_password === '') {
            $error = 'Por favor completa todos los campos.';
        } else {
            // Preparar consulta: buscar por usuario_login o por correo
            $stmt = $conexion->prepare("SELECT id, usuario_login, password, rol, nombre FROM usuarios WHERE usuario_login = ? OR correo = ? LIMIT 1");
            if ($stmt === false) {
                $error = 'Error en la consulta. Revisa la conexión.';
            } else {
                $stmt->bind_param('ss', $input_usuario, $input_usuario);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res && $res->num_rows === 1) {
                    $row = $res->fetch_assoc();
                    // Verificar contraseña hasheada
                    if (password_verify($input_password, $row['password'])) {
                        // Autenticación exitosa: inicializar sesión
                        session_regenerate_id(true);
                        $_SESSION['id'] = $row['id'];
                        $_SESSION['rol'] = $row['rol'];
                        $_SESSION['nombre'] = $row['nombre'];
                        // Limpiar token CSRF para seguridad
                        unset($_SESSION['csrf_token']);

                        // Redirigir según rol
                        if ($row['rol'] === 'admin') {
                            header('Location: admin_home.php');
                            exit;
                        } else {
                            header('Location: dashboard_user.php');
                            exit;
                        }
                    } else {
                        $error = 'Credenciales incorrectas.';
                    }
                } else {
                    $error = 'Credenciales incorrectas.';
                }
                $stmt->close();
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nequi - Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Usa tu styles.css existente -->
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Pequeño estilo de respaldo si no usas styles.css */
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;padding:20px}
    .card{max-width:420px;margin:60px auto;padding:20px;border-radius:8px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .form-group{margin-bottom:12px}
    input[type=text], input[type=password]{width:100%;padding:10px;border:1px solid #ddd;border-radius:4px}
    button{width:100%;padding:10px;border:0;background:#0066cc;color:#fff;border-radius:4px;cursor:pointer}
    .error{color:#b00020;margin-bottom:10px}
    .small-link{display:block;text-align:center;margin-top:10px}
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin-top:0">Iniciar sesión</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <div class="form-group">
        <label for="usuario">Usuario o correo</label>
        <input id="usuario" name="usuario" type="text" value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : '' ?>" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required>
      </div>

      <button type="submit">Ingresar</button>
    </form>

    <a class="small-link" href="registro.php">¿No tienes cuenta? Regístrate</a>
    <a class="small-link" href="forgot.php">¿Olvidaste tu contraseña?</a>
  </div>
</body>
</html>
