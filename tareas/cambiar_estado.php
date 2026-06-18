<?php
require_once __DIR__ . '/../config/conexion.php';

$id = (int)($_GET['id'] ?? 0);
$estadoNuevo = $_GET['estado'] ?? '';

$estadosPermitidos = ['Pendiente', 'En progreso', 'Bloqueada', 'Finalizada'];

if ($id <= 0 || !in_array($estadoNuevo, $estadosPermitidos, true)) {
    header('Location: index.php?error=Solicitud no válida');
    exit;
}

$conexion = obtenerConexion();

$sentencia = $conexion->prepare("SELECT estado FROM tarea WHERE id_tarea = :id");
$sentencia->execute([':id' => $id]);
$tarea = $sentencia->fetch();

if (!$tarea) {
    header('Location: index.php?error=Tarea no encontrada');
    exit;
}

$estadoActual = $tarea['estado'];

$transicionesValidas = [
    'Pendiente' => ['En progreso'],
    'En progreso' => ['Pendiente', 'Bloqueada', 'Finalizada'],
    'Bloqueada' => ['En progreso'],
    'Finalizada' => ['Pendiente'] // Reactivar tarea.
];

if (!in_array($estadoNuevo, $transicionesValidas[$estadoActual] ?? [], true)) {
    header('Location: index.php?error=Cambio de estado no permitido');
    exit;
}

$fechaFinalizacion = $estadoNuevo === 'Finalizada' ? date('Y-m-d H:i:s') : null;

$sentencia = $conexion->prepare("
    UPDATE tarea
    SET estado = :estado,
        fecha_finalizacion = :fecha_finalizacion
    WHERE id_tarea = :id
");

$sentencia->execute([
    ':estado' => $estadoNuevo,
    ':fecha_finalizacion' => $fechaFinalizacion,
    ':id' => $id
]);

$mensaje = $estadoNuevo === 'Finalizada'
    ? 'Tarea finalizada correctamente'
    : ($estadoActual === 'Finalizada' && $estadoNuevo === 'Pendiente'
        ? 'Tarea reactivada correctamente'
        : 'Estado actualizado correctamente');

header('Location: index.php?msg=' . urlencode($mensaje));
exit;
