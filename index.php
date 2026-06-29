<?php
$tituloPagina = 'Inicio';
$baseUrl = '.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-4 bg-light rounded">
    <h1 class="h3">Control de Tareas Personales</h1>
    <p class="mb-0">Sistema web en PHP para administrar responsables y tareas.</p>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Responsables</h2>
                <p>Crear, editar y eliminar responsables.</p>
                <a class="btn btn-primary" href="responsables/index.php">Ir a responsables</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mt-3 mt-md-0">
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Tareas</h2>
                <p>Crear, editar, eliminar y cambiar estados de tareas.</p>
                <a class="btn btn-primary" href="tareas/index.php">Ir a tareas</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mt-3">
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Grupos de tareas</h2>
                <p>Crear grupos y asociar tareas a ellos.</p>
                <a class="btn btn-primary" href="grupos/index.php">Ir a grupos</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
