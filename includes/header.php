<?php
// includes/header.php
$tituloPagina = $tituloPagina ?? 'Control de Tareas';
$baseUrl = $baseUrl ?? '.';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= $baseUrl ?>/assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>/index.php">Control de Tareas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="menuPrincipal" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/responsables/index.php">Responsables</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/tareas/index.php">Tareas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/tareas/tablero.php">Tablero</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container">
<?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
