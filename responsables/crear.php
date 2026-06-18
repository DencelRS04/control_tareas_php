<?php
require_once __DIR__ . '/../config/conexion.php';

$errores = [];
$nombre = '';
$apellidos = '';
$identificacion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $identificacion = trim($_POST['identificacion'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($apellidos === '') {
        $errores[] = 'Los apellidos son obligatorios.';
    }

    if ($identificacion === '') {
        $errores[] = 'La identificación es obligatoria.';
    }

    if (count($errores) === 0) {
        $conexion = obtenerConexion();

        $sentencia = $conexion->prepare("
            INSERT INTO responsable (nombre, apellidos, identificacion)
            VALUES (:nombre, :apellidos, :identificacion)
        ");

        $sentencia->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':identificacion' => $identificacion
        ]);

        header('Location: index.php?msg=Responsable creado correctamente');
        exit;
    }
}

$tituloPagina = 'Crear responsable';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Crear responsable</h1>

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
            <label class="form-label" for="nombre">Nombre</label>
            <input class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label" for="apellidos">Apellidos</label>
            <input class="form-control" id="apellidos" name="apellidos" value="<?= htmlspecialchars($apellidos) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label" for="identificacion">Identificación</label>
            <input class="form-control" id="identificacion" name="identificacion" value="<?= htmlspecialchars($identificacion) ?>">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="index.php">Volver</a>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
