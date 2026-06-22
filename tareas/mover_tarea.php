<?php
/**
 * mover_tarea.php
 * Endpoint AJAX usado por el tablero Kanban.
 * Reutiliza exactamente la misma lógica de transiciones de cambiar_estado.php.
 * Devuelve JSON: { ok: bool, mensaje: string }
 */
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$id          = (int)($_GET['id']     ?? 0);
$estadoNuevo = trim($_GET['estado']  ?? '');

$estadosPermitidos = ['Pendiente', 'En progreso', 'Bloqueada', 'Finalizada'];

if ($id <= 0 || !in_array($estadoNuevo, $estadosPermitidos, true)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Solicitud no válida.']);
    exit;
}

$conexion = obtenerConexion();

$sentencia = $conexion->prepare("SELECT estado FROM tarea WHERE id_tarea = :id");
$sentencia->execute([':id' => $id]);
$tarea = $sentencia->fetch();

if (!$tarea) {
    echo json_encode(['ok' => false, 'mensaje' => 'Tarea no encontrada.']);
    exit;
}

$estadoActual = $tarea['estado'];

// Mismas transiciones válidas que cambiar_estado.php
$transicionesValidas = [
    'Pendiente'   => ['En progreso'],
    'En progreso' => ['Pendiente', 'Bloqueada', 'Finalizada'],
    'Bloqueada'   => ['En progreso'],
    'Finalizada'  => ['Pendiente'],
];

if (!in_array($estadoNuevo, $transicionesValidas[$estadoActual] ?? [], true)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Cambio de estado no permitido: ' . $estadoActual . ' → ' . $estadoNuevo . '.',
    ]);
    exit;
}

$fechaFinalizacion = $estadoNuevo === 'Finalizada' ? date('Y-m-d H:i:s') : null;

$sentencia = $conexion->prepare("
    UPDATE tarea
    SET estado             = :estado,
        fecha_finalizacion = :fecha_finalizacion
    WHERE id_tarea = :id
");

$sentencia->execute([
    ':estado'             => $estadoNuevo,
    ':fecha_finalizacion' => $fechaFinalizacion,
    ':id'                 => $id,
]);

$mensaje = match (true) {
    $estadoNuevo === 'Finalizada'                          => 'Tarea finalizada correctamente.',
    $estadoActual === 'Finalizada' && $estadoNuevo === 'Pendiente' => 'Tarea reactivada correctamente.',
    default => 'Estado actualizado: ' . $estadoActual . ' → ' . $estadoNuevo . '.',
};

echo json_encode(['ok' => true, 'mensaje' => $mensaje]);
exit;
