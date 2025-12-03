<?php
// ===============================
// LOGIN FALSO ANTES DE MOSTRAR TODO
// ===============================
session_start();

if (!isset($_SESSION['fake_admin_login'])) {
    // Si no ha enviado el formulario, mostramos el login falso
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Login Admin</title>
            <style>
                body {
                    font-family: Arial;
                    background: #e3e3e3;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .login-box {
                    background: white;
                    padding: 25px;
                    border-radius: 10px;
                    width: 320px;
                    box-shadow: 0 0 5px rgba(0,0,0,0.2);
                }
                input {
                    width: 100%;
                    padding: 10px;
                    margin-bottom: 10px;
                    border-radius: 5px;
                    border: 1px solid #aaa;
                }
                button {
                    width: 100%;
                    padding: 10px;
                    background: #6f00ff;
                    border: none;
                    color: white;
                    border-radius: 5px;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>Administrador</h2>
                <p>Ingrese usuario y contraseña</p>
                <form method="POST">
                    <input type="text" name="fake_user" placeholder="Usuario" required>
                    <input type="password" name="fake_pass" placeholder="Contraseña" required>
                    <button>Ingresar</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit; // Evitamos que el index real cargue
    }

    // Cualquier usuario/clave será aceptado
    $_SESSION['fake_admin_login'] = true;
    header("Location: index.php");
    exit;
}

// ===============================
// AHORA SE MUESTRA EL INDEX REAL
// ===============================

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
