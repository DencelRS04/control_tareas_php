<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();
$id = (int)($_GET['id'] ?? 0);

$sentencia = $conexion->prepare("SELECT id_grupo FROM grupo_tarea WHERE id_grupo = :id");
$sentencia->execute([':id' => $id]);

if (!$sentencia->fetch()) {
    header('Location: index.php?error=Grupo no encontrado');
    exit;
}

$sentencia = $conexion->prepare("DELETE FROM grupo_tarea WHERE id_grupo = :id");
$sentencia->execute([':id' => $id]);

header('Location: index.php?msg=Grupo eliminado correctamente');
exit;
