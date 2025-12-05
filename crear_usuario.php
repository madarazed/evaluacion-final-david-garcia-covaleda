<?php
// crear_usuario.php
require_once "conexion.php";
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $saldo = $_POST['saldo'] ?? 0;

    if ($nombre === '') $errors[] = "Nombre requerido";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Correo inválido";
    if (strlen($password) < 4) $errors[] = "La contraseña debe tener al menos 4 caracteres";
    if (!is_numeric($saldo) || $saldo < 0) $errors[] = "Saldo inicial inválido";

    if (empty($errors)) {
        // verificar correo único
        $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Ya existe un usuario con ese correo";
        } else {
            $stmt->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, correo, password, saldo) VALUES (?,?,?,?)");
            $stmt->bind_param("sssd", $nombre, $correo, $hash, $saldo);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                // registrar transacción de saldo inicial si monto > 0
                if ((float)$saldo > 0) {
                    $tstmt = $mysqli->prepare("INSERT INTO transacciones (usuario_id, tipo, monto) VALUES (?,?,?)");
                    $tipo = 'consignar';
                    $monto = (float)$saldo;
                    $tstmt->bind_param("isd", $user_id, $tipo, $monto);
                    $tstmt->execute();
                    $tstmt->close();
                }
                $success = "Usuario creado correctamente";
                // limpiar campos
                $nombre = $correo = $saldo = "";
            } else {
                $errors[] = "Error al crear usuario";
            }
        }
        if($stmt) $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Crear usuario - Admin</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Crear usuario</h1>
          <p class="lead">Formulario para crear un usuario con saldo inicial</p>
        </div>
      </div>
      <div><a href="index.php" class="small">Volver al panel</a></div>
    </div>

    <div class="card">
      <?php if(!empty($errors)): ?>
        <div class="error">
          <?php foreach($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
      <?php endif; ?>
      <?php if($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div>
          <label>Nombre</label>
          <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre ?? ''); ?>" required>
        </div>
        <div>
          <label>Correo</label>
          <input type="email" name="correo" value="<?php echo htmlspecialchars($correo ?? ''); ?>" required>
        </div>
        <div>
          <label>Contraseña</label>
          <input type="password" name="password" required>
        </div>
        <div>
          <label>Saldo inicial (ej. 10000.00)</label>
          <input type="number" name="saldo" step="0.01" min="0" value="<?php echo htmlspecialchars($saldo ?? '0'); ?>">
        </div>

        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn" type="submit">Crear</button>
          <a class="btn" style="background:#95a5a6;text-decoration:none;display:inline-flex;align-items:center;justify-content:center" href="index.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
