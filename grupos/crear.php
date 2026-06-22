<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();

$tareasPendientes = $conexion->query("
    SELECT id_tarea, detalle
    FROM tarea
    WHERE estado = 'Pendiente'
    ORDER BY id_tarea
")->fetchAll();

$errores = [];
$nombre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $tareasSeleccionadas = $_POST['tareas'] ?? [];

    if ($nombre === '') {
        $errores[] = 'El nombre del grupo es obligatorio.';
    }

    if (count($errores) === 0) {
        $sentencia = $conexion->prepare("
            INSERT INTO grupo_tarea (nombre) VALUES (:nombre)
        ");
        $sentencia->execute([':nombre' => $nombre]);
        $idGrupo = (int)$conexion->lastInsertId();

        if (!empty($tareasSeleccionadas)) {
            $ids = array_filter(array_map('intval', $tareasSeleccionadas));
            if (!empty($ids)) {
                $placeholders = implode(',', $ids);
                $conexion->exec("
                    UPDATE tarea SET id_grupo = $idGrupo
                    WHERE id_tarea IN ($placeholders) AND estado = 'Pendiente'
                ");
            }
        }

        header('Location: index.php?msg=Grupo creado correctamente');
        exit;
    }
}

$tituloPagina = 'Crear grupo';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Crear grupo</h1>

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
            <label class="form-label" for="nombre">Nombre del grupo</label>
            <input class="form-control" type="text" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($nombre) ?>">
        </div>


        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="index.php">Volver</a>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
