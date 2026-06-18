<?php
require_once __DIR__ . '/../config/conexion.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $conexion = obtenerConexion();

    // La base de datos tiene ON DELETE SET NULL.
    // Por eso, si este responsable tiene tareas, las tareas no se eliminan;
    // simplemente quedan sin responsable asignado.
    $sentencia = $conexion->prepare("DELETE FROM responsable WHERE id_responsable = :id");
    $sentencia->execute([':id' => $id]);

    header('Location: index.php?msg=Responsable eliminado correctamente');
    exit;
}

header('Location: index.php?error=Responsable no válido');
exit;
