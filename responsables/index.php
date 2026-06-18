<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();

$sentencia = $conexion->query("
    SELECT
        r.id_responsable,
        r.nombre,
        r.apellidos,
        r.identificacion,
        COUNT(t.id_tarea) AS total_tareas
    FROM responsable r
    LEFT JOIN tarea t ON t.id_responsable = r.id_responsable
    GROUP BY r.id_responsable, r.nombre, r.apellidos, r.identificacion
    ORDER BY r.id_responsable DESC
");

$responsables = $sentencia->fetchAll();

$tituloPagina = 'Responsables';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Responsables</h1>

    <div class="d-flex gap-2">
        <a class="btn btn-secondary" href="../index.php">Volver</a>
        <a class="btn btn-primary" href="crear.php">Nuevo responsable</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($responsables) === 0): ?>
            <p class="mb-0">No hay responsables registrados.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Identificación</th>
                            <th>Tareas asignadas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($responsables as $responsable): ?>
                        <tr>
                            <td><?= htmlspecialchars($responsable['nombre']) ?></td>
                            <td><?= htmlspecialchars($responsable['apellidos']) ?></td>
                            <td><?= htmlspecialchars($responsable['identificacion']) ?></td>
                            <td><?= (int)$responsable['total_tareas'] ?></td>
                            <td class="text-end table-actions">
                                <a class="btn btn-sm btn-warning" href="editar.php?id=<?= (int)$responsable['id_responsable'] ?>">Editar</a>
                                <a class="btn btn-sm btn-danger"
                                   href="eliminar.php?id=<?= (int)$responsable['id_responsable'] ?>"
                                   onclick="return confirm('¿Desea eliminar este responsable? Las tareas quedarán sin responsable asignado.');">
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
