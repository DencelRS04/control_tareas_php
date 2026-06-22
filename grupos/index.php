<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();

$grupos = $conexion->query("
    SELECT g.id_grupo, g.nombre,
           COUNT(t.id_tarea) AS total_tareas
    FROM grupo_tarea g
    LEFT JOIN tarea t ON t.id_grupo = g.id_grupo
    GROUP BY g.id_grupo, g.nombre
    ORDER BY g.nombre
")->fetchAll();

$tituloPagina = 'Grupos de tareas';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Grupos de tareas</h1>
    <a class="btn btn-primary" href="crear.php">Nuevo grupo</a>
</div>

<?php if (count($grupos) === 0): ?>
    <p class="text-muted">No hay grupos creados.</p>
<?php else: ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tareas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grupos as $grupo): ?>
                <tr>
                    <td><?= htmlspecialchars($grupo['nombre']) ?></td>
                    <td><?= (int)$grupo['total_tareas'] ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-sm btn-info text-white" href="ver.php?id=<?= (int)$grupo['id_grupo'] ?>">Ver tareas</a>
                        <a class="btn btn-sm btn-danger"
                           href="eliminar.php?id=<?= (int)$grupo['id_grupo'] ?>"
                           onclick="return confirm('¿Eliminar este grupo? Las tareas asociadas quedarán sin grupo.')">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
