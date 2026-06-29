<?php
require_once __DIR__ . '/../config/conexion.php';

$conexion = obtenerConexion();

// ── Cargar responsables para el filtro ──────────────────────────────────────
$responsables = $conexion->query("
    SELECT id_responsable, nombre, apellidos
    FROM responsable
    ORDER BY nombre, apellidos
")->fetchAll();

// ── Leer filtros desde GET ──────────────────────────────────────────────────
$filtroPrioridad   = $_GET['prioridad']       ?? '';
$filtroResponsable = (int)($_GET['responsable'] ?? 0);
$filtroFechaDesde  = $_GET['fecha_desde']     ?? '';
$filtroFechaHasta  = $_GET['fecha_hasta']     ?? '';
$filtroEstado      = $_GET['estado']           ?? '';

// ── Construir consulta con filtros opcionales ───────────────────────────────
$condiciones = ['1=1'];
$params      = [];

if ($filtroPrioridad !== '') {
    $condiciones[] = 'prioridad = :prioridad';
    $params[':prioridad'] = $filtroPrioridad;
}

if ($filtroResponsable > 0) {
    $condiciones[] = 'id_responsable = :responsable';
    $params[':responsable'] = $filtroResponsable;
}

if ($filtroFechaDesde !== '') {
    $condiciones[] = '(fecha_limite IS NOT NULL AND fecha_limite >= :fecha_desde)';
    $params[':fecha_desde'] = $filtroFechaDesde;
}

if ($filtroFechaHasta !== '') {
    $condiciones[] = '(fecha_limite IS NOT NULL AND fecha_limite <= :fecha_hasta)';
    $params[':fecha_hasta'] = $filtroFechaHasta;
}

if ($filtroEstado !== '') {
    $condiciones[] = 'estado = :estado';
    $params[':estado'] = $filtroEstado;
}

$where = implode(' AND ', $condiciones);

$sentencia = $conexion->prepare("
    SELECT *
    FROM v_tareas_detalle
    WHERE $where
    ORDER BY
        CASE WHEN fecha_limite IS NULL THEN 1 ELSE 0 END ASC,
        fecha_limite ASC,
        id_tarea DESC
");
$sentencia->execute($params);
$todasLasTareas = $sentencia->fetchAll();

// ── Agrupar por estado ──────────────────────────────────────────────────────
$columnas = [
    'Pendiente'   => [],
    'En progreso' => [],
    'Bloqueada'   => [],
    'Finalizada'  => [],
];

foreach ($todasLasTareas as $t) {
    $columnas[$t['estado']][] = $t;
}

// ── Transiciones válidas (mismas que cambiar_estado.php) ───────────────────
$transiciones = [
    'Pendiente'   => ['En progreso'],
    'En progreso' => ['Pendiente', 'Bloqueada', 'Finalizada'],
    'Bloqueada'   => ['En progreso'],
    'Finalizada'  => ['Pendiente'],
];

// ── Helpers de badge ───────────────────────────────────────────────────────
function badgePrioridadTab(string $p): string {
    return match ($p) {
        'Alta'  => 'text-bg-danger',
        'Media' => 'text-bg-warning',
        'Baja'  => 'text-bg-success',
        default => 'text-bg-secondary',
    };
}

function colorColumna(string $estado): string {
    return match ($estado) {
        'Pendiente'   => 'secondary',
        'En progreso' => 'primary',
        'Bloqueada'   => 'dark',
        'Finalizada'  => 'success',
        default       => 'secondary',
    };
}

$tituloPagina = 'Tablero de tareas';
$baseUrl      = '..';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ── Encabezado ────────────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tablero de tareas</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-secondary" href="../index.php">Volver</a>
        <a class="btn btn-outline-secondary" href="index.php">Vista lista</a>
        <a class="btn btn-primary" href="crear.php">Nueva tarea</a>
    </div>
</div>

<!-- ── Filtros ───────────────────────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-2">
                <label class="form-label mb-1" for="f_prioridad">Prioridad</label>
                <select class="form-select form-select-sm" id="f_prioridad" name="prioridad">
                    <option value="">Todas</option>
                    <?php foreach (['Baja', 'Media', 'Alta'] as $p): ?>
                        <option value="<?= $p ?>" <?= $filtroPrioridad === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <label class="form-label mb-1" for="f_responsable">Responsable</label>
                <select class="form-select form-select-sm" id="f_responsable" name="responsable">
                    <option value="0">Todos</option>
                    <?php foreach ($responsables as $r): ?>
                        <option value="<?= (int)$r['id_responsable'] ?>"
                            <?= $filtroResponsable === (int)$r['id_responsable'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre'] . ' ' . $r['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <label class="form-label mb-1" for="f_fecha_desde">Fecha límite desde</label>
                <input class="form-control form-control-sm" type="date" id="f_fecha_desde"
                       name="fecha_desde" value="<?= htmlspecialchars($filtroFechaDesde) ?>">
            </div>

            <div class="col-sm-6 col-md-2">
                <label class="form-label mb-1" for="f_fecha_hasta">Hasta</label>
                <input class="form-control form-control-sm" type="date" id="f_fecha_hasta"
                       name="fecha_hasta" value="<?= htmlspecialchars($filtroFechaHasta) ?>">
            </div>

            <div class="col-sm-6 col-md-2">
                <label class="form-label mb-1" for="f_estado">Estado</label>
                <select class="form-select form-select-sm" id="f_estado" name="estado">
                    <option value="">Todos</option>
                    <?php foreach (['Pendiente', 'En progreso', 'Bloqueada', 'Finalizada'] as $e): ?>
                        <option value="<?= $e ?>" <?= $filtroEstado === $e ? 'selected' : '' ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary w-100" type="submit">Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary w-100" href="tablero.php">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- ── Aviso drag & drop ─────────────────────────────────────────────────── -->
<p class="text-muted small mb-3">
    <i>Arrastra una tarjeta hacia otra columna para cambiar su estado. Solo se permiten las transiciones válidas del sistema.</i>
</p>

<!-- ── Tablero Kanban ────────────────────────────────────────────────────── -->
<div class="row g-3 tablero-kanban" id="tableroKanban">

<?php foreach ($columnas as $estado => $tareas): ?>
    <?php $color = colorColumna($estado); ?>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">

            <!-- Cabecera de columna -->
            <div class="card-header bg-<?= $color ?> text-white d-flex justify-content-between align-items-center py-2">
                <strong><?= htmlspecialchars($estado) ?></strong>
                <span class="badge bg-white text-<?= $color ?> columna-contador"
                      id="contador-<?= urlencode($estado) ?>">
                    <?= count($tareas) ?>
                </span>
            </div>

            <!-- Zona de drop -->
            <div class="card-body p-2 kanban-columna"
                 id="col-<?= urlencode($estado) ?>"
                 data-estado="<?= htmlspecialchars($estado) ?>">

                <?php if (count($tareas) === 0): ?>
                    <p class="text-center text-muted small mt-3 placeholder-vacio">Sin tareas</p>
                <?php endif; ?>

                <?php foreach ($tareas as $tarea): ?>
                    <?php
                        $destinos = $transiciones[$tarea['estado']] ?? [];
                        // JSON con destinos válidos para validación en JS
                        $destinosJson = htmlspecialchars(json_encode($destinos), ENT_QUOTES);
                    ?>
                    <div class="card kanban-card mb-2 <?= $tarea['estado'] === 'Finalizada' ? 'opacity-75' : '' ?>"
                         draggable="true"
                         data-id="<?= (int)$tarea['id_tarea'] ?>"
                         data-estado-actual="<?= htmlspecialchars($tarea['estado']) ?>"
                         data-destinos-validos="<?= $destinosJson ?>">

                        <div class="card-body p-2">

                            <!-- Detalle -->
                            <p class="mb-2 small <?= $tarea['estado'] === 'Finalizada' ? 'text-decoration-line-through text-muted' : '' ?>">
                                <?= htmlspecialchars($tarea['detalle']) ?>
                            </p>

                            <!-- Badges de prioridad -->
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge <?= badgePrioridadTab($tarea['prioridad']) ?>" style="font-size:.7rem">
                                    <?= htmlspecialchars($tarea['prioridad']) ?>
                                </span>
                                <?php if ($tarea['fecha_limite']): ?>
                                    <span class="badge text-bg-light border small" style="font-size:.7rem">
                                        <?= htmlspecialchars($tarea['fecha_limite']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Responsable -->
                            <p class="text-muted mb-2" style="font-size:.75rem">
                                <?= htmlspecialchars($tarea['responsable']) ?>
                            </p>

                            <!-- Acciones rápidas -->
                            <div class="d-flex gap-1 flex-wrap">
                                <a class="btn btn-sm btn-warning py-0 px-2" style="font-size:.75rem"
                                   href="editar.php?id=<?= (int)$tarea['id_tarea'] ?>">Editar</a>
                                <a class="btn btn-sm btn-danger py-0 px-2" style="font-size:.75rem"
                                   href="eliminar.php?id=<?= (int)$tarea['id_tarea'] ?>"
                                   onclick="return confirm('¿Desea eliminar esta tarea?');">Eliminar</a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div><!-- /kanban-columna -->
        </div>
    </div>

<?php endforeach; ?>

</div><!-- /tableroKanban -->

<!-- ── Toast de notificación ─────────────────────────────────────────────── -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastKanban" class="toast align-items-center border-0" role="alert" aria-live="assertive">
        <div class="d-flex">
            <div class="toast-body" id="toastMensaje"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- ── CSS extra ─────────────────────────────────────────────────────────── -->
<style>
.kanban-columna {
    min-height: 120px;
    transition: background-color .15s;
}
.kanban-columna.drag-over {
    background-color: #e9ecef;
    border-radius: .375rem;
}
.kanban-card {
    cursor: grab;
    user-select: none;
    border-left: 4px solid #dee2e6;
    transition: box-shadow .15s, opacity .15s;
}
.kanban-card:active {
    cursor: grabbing;
}
.kanban-card.dragging {
    opacity: .45;
    box-shadow: 0 4px 12px rgba(0,0,0,.25);
}
</style>

<!-- ── JavaScript drag & drop ────────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    // ── Referencias ────────────────────────────────────────────────────────
    const columnas = document.querySelectorAll('.kanban-columna');
    const toast    = new bootstrap.Toast(document.getElementById('toastKanban'), { delay: 3000 });
    const toastMsg = document.getElementById('toastMensaje');
    let   tarjetaArrastrada = null;

    // ── Helpers ────────────────────────────────────────────────────────────
    function mostrarToast(mensaje, exito = true) {
        const el = document.getElementById('toastKanban');
        el.classList.remove('text-bg-success', 'text-bg-danger');
        el.classList.add(exito ? 'text-bg-success' : 'text-bg-danger');
        toastMsg.textContent = mensaje;
        toast.show();
    }

    function actualizarContador(estadoId) {
        const col = document.getElementById('col-' + estadoId);
        if (!col) return;
        const n   = col.querySelectorAll('.kanban-card').length;
        const cnt = document.getElementById('contador-' + estadoId);
        if (cnt) cnt.textContent = n;

        // Mostrar / ocultar placeholder
        let placeholder = col.querySelector('.placeholder-vacio');
        if (n === 0) {
            if (!placeholder) {
                placeholder = document.createElement('p');
                placeholder.className = 'text-center text-muted small mt-3 placeholder-vacio';
                placeholder.textContent = 'Sin tareas';
                col.appendChild(placeholder);
            }
        } else if (placeholder) {
            placeholder.remove();
        }
    }

    // ── Eventos en las tarjetas ────────────────────────────────────────────
    document.getElementById('tableroKanban').addEventListener('dragstart', function (e) {
        const card = e.target.closest('.kanban-card');
        if (!card) return;
        tarjetaArrastrada = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.dataset.id);
    });

    document.getElementById('tableroKanban').addEventListener('dragend', function (e) {
        const card = e.target.closest('.kanban-card');
        if (card) card.classList.remove('dragging');
        columnas.forEach(c => c.classList.remove('drag-over'));
        tarjetaArrastrada = null;
    });

    // ── Eventos en las columnas ────────────────────────────────────────────
    columnas.forEach(function (col) {

        col.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('drag-over');
        });

        col.addEventListener('dragleave', function () {
            col.classList.remove('drag-over');
        });

        col.addEventListener('drop', function (e) {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!tarjetaArrastrada) return;

            const estadoDestino = col.dataset.estado;
            const estadoOrigen  = tarjetaArrastrada.dataset.estadoActual;
            const idTarea       = tarjetaArrastrada.dataset.id;

            // No hacer nada si se suelta en la misma columna.
            if (estadoDestino === estadoOrigen) return;

            // Validar transición en el cliente.
            let destinosValidos = [];
            try {
                destinosValidos = JSON.parse(tarjetaArrastrada.dataset.destinosValidos);
            } catch (_) {}

            if (!destinosValidos.includes(estadoDestino)) {
                mostrarToast('Cambio de estado no permitido: ' + estadoOrigen + ' → ' + estadoDestino, false);
                return;
            }

            // ── Llamada AJAX al servidor ───────────────────────────────────
            const url = 'mover_tarea.php?id=' + encodeURIComponent(idTarea)
                      + '&estado='            + encodeURIComponent(estadoDestino);

            fetch(url)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        // Mover la tarjeta al DOM de la columna destino.
                        const colOrigen  = document.getElementById('col-' + encodeURIComponent(estadoOrigen));
                        const colDestino = col;

                        // Actualizar atributos de la tarjeta.
                        tarjetaArrastrada.dataset.estadoActual = estadoDestino;

                        // Calcular nuevas transiciones para la tarjeta.
                        const mapa = {
                            'Pendiente':   ['En progreso'],
                            'En progreso': ['Pendiente', 'Bloqueada', 'Finalizada'],
                            'Bloqueada':   ['En progreso'],
                            'Finalizada':  ['Pendiente'],
                        };
                        tarjetaArrastrada.dataset.destinosValidos = JSON.stringify(mapa[estadoDestino] || []);

                        // Ajustar estilo de "finalizada".
                        const detalleEl = tarjetaArrastrada.querySelector('p.small');
                        if (estadoDestino === 'Finalizada') {
                            tarjetaArrastrada.classList.add('opacity-75');
                            if (detalleEl) detalleEl.classList.add('text-decoration-line-through', 'text-muted');
                        } else {
                            tarjetaArrastrada.classList.remove('opacity-75');
                            if (detalleEl) detalleEl.classList.remove('text-decoration-line-through', 'text-muted');
                        }

                        colDestino.appendChild(tarjetaArrastrada);
                        actualizarContador(encodeURIComponent(estadoOrigen));
                        actualizarContador(encodeURIComponent(estadoDestino));
                        mostrarToast(data.mensaje, true);
                    } else {
                        mostrarToast(data.mensaje || 'No se pudo cambiar el estado.', false);
                    }
                })
                .catch(function () {
                    mostrarToast('Error de comunicación con el servidor.', false);
                });
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
