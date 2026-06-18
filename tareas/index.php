<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();

$sentencia = $conexion->query("
    SELECT *
    FROM v_tareas_detalle
    ORDER BY orden_finalizada ASC,
             CASE WHEN fecha_limite IS NULL THEN 1 ELSE 0 END ASC,
             fecha_limite ASC,
             id_tarea DESC
");

$tareas = $sentencia->fetchAll();

function badgePrioridad(string $prioridad): string
{
    return match ($prioridad) {
        'Alta' => 'text-bg-danger',
        'Media' => 'text-bg-warning',
        'Baja' => 'text-bg-success',
        default => 'text-bg-secondary'
    };
}

function badgeEstado(string $estado): string
{
    return match ($estado) {
        'Pendiente' => 'text-bg-secondary',
        'En progreso' => 'text-bg-primary',
        'Bloqueada' => 'text-bg-dark',
        'Finalizada' => 'text-bg-success',
        default => 'text-bg-secondary'
    };
}

$tituloPagina = 'Tareas';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tareas</h1>

    <div class="d-flex gap-2">
        <a class="btn btn-secondary" href="../index.php">Volver</a>
        <a class="btn btn-primary" href="crear.php">Nueva tarea</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($tareas) === 0): ?>
            <p class="mb-0">No hay tareas registradas.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Detalle</th>
                            <th>Responsable</th>
                            <th>Prioridad</th>
                            <th>Fecha límite</th>
                            <th>Estado</th>
                            <th>Finalización</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tareas as $tarea): ?>
                        <tr>
                            <td class="<?= $tarea['estado'] === 'Finalizada' ? 'finalizada' : '' ?>">
                                <?= htmlspecialchars($tarea['detalle']) ?>
                            </td>
                            <td><?= htmlspecialchars($tarea['responsable']) ?></td>
                            <td>
                                <span class="badge badge-prioridad <?= badgePrioridad($tarea['prioridad']) ?>">
                                    <?= htmlspecialchars($tarea['prioridad']) ?>
                                </span>
                            </td>
                            <td><?= $tarea['fecha_limite'] ? htmlspecialchars($tarea['fecha_limite']) : 'Sin fecha' ?></td>
                            <td>
                                <span class="badge <?= badgeEstado($tarea['estado']) ?>">
                                    <?= htmlspecialchars($tarea['estado']) ?>
                                </span>
                            </td>
                            <td><?= $tarea['fecha_finalizacion'] ? htmlspecialchars($tarea['fecha_finalizacion']) : '-' ?></td>
                            <td class="text-end table-actions">
                                <?php if ($tarea['estado'] === 'Pendiente'): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=En%20progreso">En progreso</a>
                                <?php elseif ($tarea['estado'] === 'En progreso'): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=Pendiente">Pendiente</a>
                                    <a class="btn btn-sm btn-outline-dark" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=Bloqueada">Bloquear</a>
                                    <a class="btn btn-sm btn-outline-success" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=Finalizada">Finalizar</a>
                                <?php elseif ($tarea['estado'] === 'Bloqueada'): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=En%20progreso">En progreso</a>
                                <?php elseif ($tarea['estado'] === 'Finalizada'): ?>
                                    <a class="btn btn-sm btn-outline-warning" href="cambiar_estado.php?id=<?= (int)$tarea['id_tarea'] ?>&estado=Pendiente">Reactivar</a>
                                <?php endif; ?>

                                <a class="btn btn-sm btn-warning" href="editar.php?id=<?= (int)$tarea['id_tarea'] ?>">Editar</a>
                                <a class="btn btn-sm btn-danger"
                                   href="eliminar.php?id=<?= (int)$tarea['id_tarea'] ?>"
                                   onclick="return confirm('¿Desea eliminar esta tarea?');">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
