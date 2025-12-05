<?php
require_once "conexion.php";
$msg = "";
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
}

// obtener usuarios
$stmt = $mysqli->prepare("SELECT id, nombre, correo, saldo, creado_en FROM usuarios ORDER BY creado_en DESC");
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Administrador - Nequi</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Nequi (Admin)</h1>
          <p class="lead">Panel administrativo</p>
        </div>
      </div>
      <div class="small">Administrador</div>
    </div>

    <div class="grid">
      <div class="card">
        <?php if($msg): ?>
          <div class="<?php echo (strpos($msg,'Error')===0)?'error':'success'; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <h2>Usuarios</h2>
        <?php if(count($usuarios) === 0): ?>
          <p class="small">No hay usuarios aún. Usa "Crear usuario" a la derecha.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Saldo</th>
                <th>Creado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($usuarios as $u): ?>
                <tr>
                  <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($u['correo']); ?></td>
                  <td>$ <?php echo number_format($u['saldo'],2,',','.'); ?></td>
                  <td class="small"><?php echo $u['creado_en']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <div class="footer">
          <small>Panel simple — solo vista Administrador</small>
        </div>
      </div>

      <aside class="card">
        <h3>Acciones</h3>
        <div class="menu">
          <a href="crear_usuario.php">Crear usuario</a>
          <a href="consignar.php">Consignar</a>
          <a href="retirar.php">Retirar</a>
          <a href="consultar.php">Consultar saldo / historial</a>
        </div>
      </aside>
    </div>
  </div>
</body>
</html>
