<?php
require_once __DIR__ . '/../config/conexion.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare("DELETE FROM tarea WHERE id_tarea = :id");
    $sentencia->execute([':id' => $id]);

    header('Location: index.php?msg=Tarea eliminada correctamente');
    exit;
}

header('Location: index.php?error=Tarea no válida');
exit;
