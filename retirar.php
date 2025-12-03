<?php
// retirar.php
require_once "conexion.php";
$errors = [];
$success = "";

$usuarios = [];
$r = $mysqli->query("SELECT id, nombre, correo, saldo FROM usuarios ORDER BY nombre ASC");
if ($r) $usuarios = $r->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    $monto = $_POST['monto'] ?? 0;

    if ($usuario_id <= 0) $errors[] = "Selecciona un usuario";
    if (!is_numeric($monto) || $monto <= 0) $errors[] = "Monto inválido";

    // obtener saldo actual
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT saldo FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $errors[] = "Usuario no encontrado";
            $stmt->close();
        } else {
            $row = $res->fetch_assoc();
            $current = (float)$row['saldo'];
            $stmt->close();
            if ($current < (float)$monto) {
                $errors[] = "Saldo insuficiente (saldo actual: $current)";
            } else {
                // proceder a retirar
                $mysqli->begin_transaction();
                try {
                    $ustmt = $mysqli->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ?");
                    $ustmt->bind_param("di", $monto, $usuario_id);
                    if (!$ustmt->execute() || $ustmt->affected_rows === 0) {
                        throw new Exception("Error al actualizar saldo");
                    }
                    $ustmt->close();

                    $tstmt = $mysqli->prepare("INSERT INTO transacciones (usuario_id, tipo, monto) VALUES (?,?,?)");
                    $tipo = 'retirar';
                    $tstmt->bind_param("isd", $usuario_id, $tipo, $monto);
                    $tstmt->execute();
                    $tstmt->close();

                    $mysqli->commit();
                    $success = "Retiro exitoso.";
                } catch (Exception $e) {
                    $mysqli->rollback();
                    $errors[] = "Error: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Retirar - Admin</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Retirar</h1>
          <p class="lead">Retirar saldo de un usuario</p>
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
          <label>Seleccionar usuario</label>
          <select name="usuario_id" required>
            <option value="">-- Seleccione --</option>
            <?php foreach($usuarios as $u): ?>
              <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nombre'] . " — " . $u['correo'] . " (Saldo: $".number_format($u['saldo'],2,',','.').")"); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Monto a retirar</label>
          <input type="number" name="monto" step="0.01" min="0.01" required>
        </div>

        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn" type="submit">Retirar</button>
          <a class="btn" style="background:#95a5a6;text-decoration:none;display:inline-flex;align-items:center;justify-content:center" href="index.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
