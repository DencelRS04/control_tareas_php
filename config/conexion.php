<?php
// config/conexion.php
// Ajuste estos datos según su entorno local o hosting.

define('DB_HOST', '138.59.135.33');
define('DB_NAME', 'control_tareas');
define('DB_USER', 'Deprabados');
define('DB_PASS', 'denceljasan2004');

function obtenerConexion(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $conexion = new PDO($dsn, DB_USER, DB_PASS);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conexion;
    } catch (PDOException $e) {
        die('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}
