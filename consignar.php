<?php
// consignar.php
require_once "conexion.php";
$errors = [];
$success = "";

$usuarios = [];
$r = $mysqli->query("SELECT id, nombre, correo FROM usuarios ORDER BY nombre ASC");
if ($r) $usuarios = $r->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    $monto = $_POST['monto'] ?? 0;

    if ($usuario_id <= 0) $errors[] = "Selecciona un usuario";
    if (!is_numeric($monto) || $monto <= 0) $errors[] = "Monto inválido";

    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            // actualizar saldo
            $stmt = $mysqli->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
            $stmt->bind_param("di", $monto, $usuario_id);
            if (!$stmt->execute() || $stmt->affected_rows === 0) {
                throw new Exception("Error al actualizar saldo");
            }
            $stmt->close();

            // registrar transacción
            $tstmt = $mysqli->prepare("INSERT INTO transacciones (usuario_id, tipo, monto) VALUES (?,?,?)");
            $tipo = 'consignar';
            $tstmt->bind_param("isd", $usuario_id, $tipo, $monto);
            $tstmt->execute();
            $tstmt->close();

            $mysqli->commit();
            $success = "Consignación exitosa.";
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Consignar - Admin</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Consignar</h1>
          <p class="lead">Agregar saldo a un usuario</p>
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
              <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nombre'] . " — " . $u['correo']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Monto a consignar</label>
          <input type="number" name="monto" step="0.01" min="0.01" required>
        </div>

        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn" type="submit">Consignar</button>
          <a class="btn" style="background:#95a5a6;text-decoration:none;display:inline-flex;align-items:center;justify-content:center" href="index.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
