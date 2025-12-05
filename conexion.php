<?php
// conexion.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = ""; 
$DB_NAME = "nequi_simple";

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Error de conexión MySQL: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
?>
