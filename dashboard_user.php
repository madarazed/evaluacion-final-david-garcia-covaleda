<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: index.php?msg=Debes iniciar sesión");
    exit();
}


// Datos del usuario logueado
$user_id = $_SESSION['usuario_id'];

$stmt = $mysqli->prepare("SELECT nombre, correo, saldo, creado_en FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener historial de transacciones
$stmt = $mysqli->prepare("SELECT tipo, monto, fecha FROM transacciones WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$trans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mi cuenta - Nequi</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">

    <div class="header">
      <div class="brand">
        <div class="logo">Nequi</div>
        <div>
          <h1>Mi Cuenta</h1>
          <p class="lead">Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></p>
        </div>
      </div>
      <div class="small">
        Usuario
      </div>
    </div>

    <div class="grid">

      <!-- TARJETA PRINCIPAL -->
      <div class="card">
        <h2>Resumen de cuenta</h2>

        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['nombre']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['correo']); ?></p>
        <p><strong>Saldo actual:</strong> <span style="color:#00a3e0;font-weight:bold;">$ 
          <?php echo number_format($user['saldo'], 2, ',', '.'); ?>
        </span></p>

        <hr>

        <h2>Historial de movimientos</h2>

        <?php if (count($trans) === 0): ?>
          <p class="small">No tienes movimientos aún.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($trans as $t): ?>
                <tr>
                  <td><?php echo ucfirst($t['tipo']); ?></td>
                  <td>$ <?php echo number_format($t['monto'], 2, ',', '.'); ?></td>
                  <td class="small"><?php echo $t['fecha']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <div class="footer">
          <small>Nequi Simple — Vista Usuario</small>
        </div>
      </div>

      <!-- MENU LATERAL: versión reducida -->
      <aside class="card">
          <a href="logout.php">Cerrar sesión</a>
        </div>
      </aside>

    </div>

  </div>
</body>
</html>
