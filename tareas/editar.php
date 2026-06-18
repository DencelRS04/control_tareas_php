<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();
$id = (int)($_GET['id'] ?? 0);

$sentencia = $conexion->prepare("SELECT * FROM tarea WHERE id_tarea = :id");
$sentencia->execute([':id' => $id]);
$tarea = $sentencia->fetch();

if (!$tarea) {
    header('Location: index.php?error=Tarea no encontrada');
    exit;
}

$responsables = $conexion->query("
    SELECT id_responsable, nombre, apellidos
    FROM responsable
    ORDER BY nombre, apellidos
")->fetchAll();

$errores = [];
$detalle = $tarea['detalle'];
$idResponsable = $tarea['id_responsable'] ?? '';
$prioridad = $tarea['prioridad'];
$fechaLimite = $tarea['fecha_limite'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $detalle = trim($_POST['detalle'] ?? '');
    $idResponsable = $_POST['id_responsable'] ?? '';
    $prioridad = $_POST['prioridad'] ?? 'Media';
    $fechaLimite = trim($_POST['fecha_limite'] ?? '');

    $prioridadesPermitidas = ['Baja', 'Media', 'Alta'];

    if ($detalle === '') {
        $errores[] = 'El detalle de la tarea es obligatorio.';
    }

    if (!in_array($prioridad, $prioridadesPermitidas, true)) {
        $errores[] = 'La prioridad seleccionada no es válida.';
    }

    $idResponsableBD = $idResponsable === '' ? null : (int)$idResponsable;
    $fechaLimiteBD = $fechaLimite === '' ? null : $fechaLimite;

    if (count($errores) === 0) {
        $sentencia = $conexion->prepare("
            UPDATE tarea
            SET detalle = :detalle,
                id_responsable = :id_responsable,
                prioridad = :prioridad,
                fecha_limite = :fecha_limite
            WHERE id_tarea = :id
        ");

        $sentencia->execute([
            ':detalle' => $detalle,
            ':id_responsable' => $idResponsableBD,
            ':prioridad' => $prioridad,
            ':fecha_limite' => $fechaLimiteBD,
            ':id' => $id
        ]);

        header('Location: index.php?msg=Tarea actualizada correctamente');
        exit;
    }
}

$tituloPagina = 'Editar tarea';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Editar tarea</h1>

<?php if (count($errores) > 0): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="detalle">Detalle</label>
            <textarea class="form-control" id="detalle" name="detalle" rows="4"><?= htmlspecialchars($detalle) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label" for="id_responsable">Responsable</label>
            <select class="form-select" id="id_responsable" name="id_responsable">
                <option value="">Sin responsable asignado</option>
                <?php foreach ($responsables as $responsable): ?>
                    <?php $seleccionado = ((string)$responsable['id_responsable'] === (string)$idResponsable) ? 'selected' : ''; ?>
                    <option value="<?= (int)$responsable['id_responsable'] ?>" <?= $seleccionado ?>>
                        <?= htmlspecialchars($responsable['nombre'] . ' ' . $responsable['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="prioridad">Prioridad</label>
            <select class="form-select" id="prioridad" name="prioridad">
                <option value="Baja" <?= $prioridad === 'Baja' ? 'selected' : '' ?>>Baja</option>
                <option value="Media" <?= $prioridad === 'Media' ? 'selected' : '' ?>>Media</option>
                <option value="Alta" <?= $prioridad === 'Alta' ? 'selected' : '' ?>>Alta</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="fecha_limite">Fecha límite</label>
            <input class="form-control" type="date" id="fecha_limite" name="fecha_limite" value="<?= htmlspecialchars((string)$fechaLimite) ?>">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="index.php">Volver</a>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
