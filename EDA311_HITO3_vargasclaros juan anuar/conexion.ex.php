<?php
$host    = "10.0.40.85";  
$puerto  = 3306;
$usuario = "u_vargas_juan";
$clave   = "Mar#26T3gN9";
$bd      = "tienda_vargas";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $usuario, $clave, $bd, $puerto);

if ($conn->connect_errno) {
    $errorConexion = $conn->connect_error;
    $conn = null;
} else {
    $conn->set_charset("utf8mb4");
    $errorConexion = null;
}
