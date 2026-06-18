<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();
$id = (int)($_GET['id'] ?? 0);

$sentencia = $conexion->prepare("SELECT * FROM responsable WHERE id_responsable = :id");
$sentencia->execute([':id' => $id]);
$responsable = $sentencia->fetch();

if (!$responsable) {
    header('Location: index.php?error=Responsable no encontrado');
    exit;
}

$errores = [];
$nombre = $responsable['nombre'];
$apellidos = $responsable['apellidos'];
$identificacion = $responsable['identificacion'];

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
        $sentencia = $conexion->prepare("
            UPDATE responsable
            SET nombre = :nombre,
                apellidos = :apellidos,
                identificacion = :identificacion
            WHERE id_responsable = :id
        ");

        $sentencia->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':identificacion' => $identificacion,
            ':id' => $id
        ]);

        header('Location: index.php?msg=Responsable actualizado correctamente');
        exit;
    }
}

$tituloPagina = 'Editar responsable';
$baseUrl = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Editar responsable</h1>

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
