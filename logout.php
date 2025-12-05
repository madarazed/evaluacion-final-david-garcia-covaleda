<?php
session_start();

// Elimina todas las variables de sesión
$_SESSION = [];

// Destruye la sesión
session_destroy();

// Redirección al login
header("Location: index.php?msg=Sesion cerrada correctamente");
exit();
