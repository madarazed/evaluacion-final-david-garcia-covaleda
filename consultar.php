<?php
// consultar.php
require_once "conexion.php";

$usuarios = [];
$r = $mysqli->query("SELECT id, nombre, correo FROM usuarios ORDER BY nombre ASC");
if ($r) $usuarios = $r->fetch_all(MYSQLI_ASSOC);

$selected = null;
$transacciones = [];
$saldo = null;
if (isset($_GET['usuario_id'])) {
    $usuario_id = intval($_GET['usuario_id']);
    $stmt = $mysqli->prepare("SELECT id, nombre, correo, saldo, creado_en FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $selected = $res->fetch_assoc();
        $saldo = $selected['saldo'];

        $tstmt = $mysqli->prepare("SELECT tipo, monto, fecha FROM transacciones WHERE usuario_id = ? ORDER BY fecha DESC");
        $tstmt->bind_param("i", $usuario_id);
        $tstmt->execute();
        $tres = $tstmt->get_result();
        $transacciones = $tres->fetch_all(MYSQLI_ASSOC);
        $tstmt->close();
    }
    $stmt->close();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Consultar - Admin</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Consultar saldo / historial</h1>
          <p class="lead">Selecciona un usuario para ver su saldo y las transacciones</p>
        </div>
      </div>
      <div><a href="index.php" class="small">Volver al panel</a></div>
    </div>

    <div class="card">
      <form method="get" action="">
        <div>
          <label>Seleccionar usuario</label>
          <select name="usuario_id" onchange="this.form.submit()">
            <option value="">-- Seleccione --</option>
            <?php foreach($usuarios as $u): ?>
              <option value="<?php echo $u['id']; ?>" <?php echo (isset($selected) && $selected['id']==$u['id'])?'selected':''; ?>>
                <?php echo htmlspecialchars($u['nombre'] . " — " . $u['correo']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>

      <?php if($selected): ?>
        <h3 style="margin-top:12px"><?php echo htmlspecialchars($selected['nombre']); ?> — <?php echo htmlspecialchars($selected['correo']); ?></h3>
        <p><strong>Saldo: </strong>$ <?php echo number_format($saldo,2,',','.'); ?></p>

        <h4>Transacciones</h4>
        <?php if(empty($transacciones)): ?>
          <p class="small">El usuario no tiene transacciones registradas.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>Tipo</th><th>Monto</th><th>Fecha</th></tr></thead>
            <tbody>
              <?php foreach($transacciones as $t): ?>
                <tr>
                  <td><?php echo htmlspecialchars($t['tipo']); ?></td>
                  <td>$ <?php echo number_format($t['monto'],2,',','.'); ?></td>
                  <td class="small"><?php echo $t['fecha']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</body>
</html>

