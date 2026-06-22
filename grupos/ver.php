<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();
$id = (int)($_GET['id'] ?? 0);

$sentGrupo = $conexion->prepare("SELECT * FROM grupo_tarea WHERE id_grupo = :id");
$sentGrupo->execute([':id' => $id]);
$grupo = $sentGrupo->fetch();

if (!$grupo) {
    header('Location: index.php?error=Grupo no encontrado');
    exit;
}

$sentTareas = $conexion->prepare("
    SELECT *
    FROM v_tareas_detalle
    WHERE id_grupo = :id
    ORDER BY orden_finalizada ASC, fecha_limite ASC
");
$sentTareas->execute([':id' => $id]);
$tareas = $sentTareas->fetchAll();

$tituloPagina = 'Tareas del grupo: ' . $grupo['nombre'];
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Grupo: <?= htmlspecialchars($grupo['nombre']) ?></h1>
    <a class="btn btn-secondary" href="index.php">Volver a grupos</a>
</div>

<?php if (count($tareas) === 0): ?>
    <p class="text-muted">Este grupo no tiene tareas asociadas.</p>
<?php else: ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Detalle</th>
                <th>Responsable</th>
                <th>Prioridad</th>
                <th>Fecha límite</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tareas as $tarea): ?>
                <tr>
                    <td <?= $tarea['estado'] === 'Finalizada' ? 'class="text-decoration-line-through text-muted"' : '' ?>>
                        <?= htmlspecialchars($tarea['detalle']) ?>
                    </td>
                    <td><?= htmlspecialchars($tarea['responsable']) ?></td>
                    <td><?= htmlspecialchars($tarea['prioridad']) ?></td>
                    <td><?= $tarea['fecha_limite'] ? htmlspecialchars($tarea['fecha_limite']) : '—' ?></td>
                    <td><?= htmlspecialchars($tarea['estado']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-warning"
                           href="../tareas/editar.php?id=<?= (int)$tarea['id_tarea'] ?>">
                            Editar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
