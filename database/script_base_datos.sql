-- ============================================================
-- Tarea Corta 2 - Control de Tareas Personales
-- Motor: MySQL / MariaDB
-- Base de datos completa para las HU:
-- 1) Responsables
-- 2) Tareas
-- 3) Grupos de tareas
-- 4) Tablero / filtros
-- ============================================================

CREATE DATABASE IF NOT EXISTS control_tareas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE control_tareas;

-- Se eliminan objetos en orden correcto por dependencias.
DROP VIEW IF EXISTS v_tareas_detalle;
DROP PROCEDURE IF EXISTS sp_cambiar_estado_tarea;
DROP TRIGGER IF EXISTS trg_tarea_bi;
DROP TRIGGER IF EXISTS trg_tarea_bu;

DROP TABLE IF EXISTS tarea;
DROP TABLE IF EXISTS grupo_tarea;
DROP TABLE IF EXISTS responsable;

-- ============================================================
-- Tabla: responsable
-- Uso: permite crear, editar y eliminar responsables.
-- ============================================================
CREATE TABLE responsable (
    id_responsable INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    apellidos VARCHAR(120) NOT NULL,
    identificacion VARCHAR(30) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Tabla: grupo_tarea
-- Uso: permite agrupar tareas.
-- ============================================================
CREATE TABLE grupo_tarea (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Tabla: tarea
-- Uso: permite registrar tareas, responsable opcional, grupo opcional,
-- prioridad, fecha límite opcional, estado y fecha de finalización.
-- ============================================================
CREATE TABLE tarea (
    id_tarea INT AUTO_INCREMENT PRIMARY KEY,
    detalle TEXT NOT NULL,
    id_responsable INT NULL,
    id_grupo INT NULL,
    prioridad ENUM('Baja', 'Media', 'Alta') NOT NULL DEFAULT 'Media',
    fecha_limite DATE NULL,
    estado ENUM('Pendiente', 'En progreso', 'Bloqueada', 'Finalizada') NOT NULL DEFAULT 'Pendiente',
    fecha_finalizacion DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tarea_responsable
        FOREIGN KEY (id_responsable)
        REFERENCES responsable(id_responsable)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_tarea_grupo
        FOREIGN KEY (id_grupo)
        REFERENCES grupo_tarea(id_grupo)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_tarea_estado ON tarea(estado);
CREATE INDEX idx_tarea_prioridad ON tarea(prioridad);
CREATE INDEX idx_tarea_fecha_limite ON tarea(fecha_limite);
CREATE INDEX idx_tarea_responsable ON tarea(id_responsable);
CREATE INDEX idx_tarea_grupo ON tarea(id_grupo);

-- ============================================================
-- Vista: v_tareas_detalle
-- Uso: centraliza el texto "Sin responsable asignado"
-- cuando la tarea no tiene responsable.
-- También ayuda para listados y tablero.
-- ============================================================
CREATE VIEW v_tareas_detalle AS
SELECT
    t.id_tarea,
    t.detalle,
    t.id_responsable,
    COALESCE(CONCAT(r.nombre, ' ', r.apellidos), 'Sin responsable asignado') AS responsable,
    r.identificacion AS identificacion_responsable,
    t.id_grupo,
    COALESCE(g.nombre, 'Sin grupo') AS grupo,
    t.prioridad,
    t.fecha_limite,
    t.estado,
    t.fecha_finalizacion,
    t.fecha_creacion,
    CASE WHEN t.estado = 'Finalizada' THEN 1 ELSE 0 END AS orden_finalizada
FROM tarea t
LEFT JOIN responsable r ON r.id_responsable = t.id_responsable
LEFT JOIN grupo_tarea g ON g.id_grupo = t.id_grupo;

DELIMITER $$

-- ============================================================
-- Trigger: antes de insertar tarea.
-- Si una tarea entra finalizada, asigna fecha de finalización.
-- Si no está finalizada, asegura que no tenga fecha de finalización.
-- ============================================================
CREATE TRIGGER trg_tarea_bi
BEFORE INSERT ON tarea
FOR EACH ROW
BEGIN
    IF NEW.estado = 'Finalizada' AND NEW.fecha_finalizacion IS NULL THEN
        SET NEW.fecha_finalizacion = NOW();
    END IF;

    IF NEW.estado <> 'Finalizada' THEN
        SET NEW.fecha_finalizacion = NULL;
    END IF;
END$$

-- ============================================================
-- Trigger: antes de actualizar tarea.
-- Mantiene coherente fecha_finalizacion según el estado.
-- ============================================================
CREATE TRIGGER trg_tarea_bu
BEFORE UPDATE ON tarea
FOR EACH ROW
BEGIN
    IF NEW.estado = 'Finalizada' AND NEW.fecha_finalizacion IS NULL THEN
        SET NEW.fecha_finalizacion = NOW();
    END IF;

    IF NEW.estado <> 'Finalizada' THEN
        SET NEW.fecha_finalizacion = NULL;
    END IF;
END$$

-- ============================================================
-- Procedimiento: sp_cambiar_estado_tarea
-- Valida los cambios de estado solicitados:
-- Pendiente <-> En progreso
-- En progreso <-> Bloqueada
-- En progreso -> Finalizada
-- Bloqueada -> En progreso
-- Finalizada -> Pendiente  (reactivar tarea)
-- ============================================================
CREATE PROCEDURE sp_cambiar_estado_tarea(
    IN p_id_tarea INT,
    IN p_estado_nuevo VARCHAR(20)
)
BEGIN
    DECLARE v_estado_actual VARCHAR(20);
    DECLARE v_total INT DEFAULT 0;

    SELECT COUNT(*), MAX(estado)
    INTO v_total, v_estado_actual
    FROM tarea
    WHERE id_tarea = p_id_tarea;

    IF v_total = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La tarea indicada no existe.';
    END IF;

    IF (
        (v_estado_actual = 'Pendiente' AND p_estado_nuevo = 'En progreso')
        OR (v_estado_actual = 'En progreso' AND p_estado_nuevo = 'Pendiente')
        OR (v_estado_actual = 'En progreso' AND p_estado_nuevo = 'Bloqueada')
        OR (v_estado_actual = 'Bloqueada' AND p_estado_nuevo = 'En progreso')
        OR (v_estado_actual = 'En progreso' AND p_estado_nuevo = 'Finalizada')
        OR (v_estado_actual = 'Finalizada' AND p_estado_nuevo = 'Pendiente')
    ) THEN
        UPDATE tarea
        SET
            estado = p_estado_nuevo,
            fecha_finalizacion = CASE
                WHEN p_estado_nuevo = 'Finalizada' THEN NOW()
                ELSE NULL
            END
        WHERE id_tarea = p_id_tarea;
    ELSE
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cambio de estado no permitido.';
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- Datos de prueba opcionales.
-- Puede borrarlos si desea entregar la base vacía.
-- ============================================================
INSERT INTO responsable (nombre, apellidos, identificacion) VALUES
('Carlos', 'Mora Solano', '101110111'),
('María', 'Rodríguez Arias', '202220222');

INSERT INTO grupo_tarea (nombre) VALUES
('Universidad'),
('Trabajo');

INSERT INTO tarea (detalle, id_responsable, id_grupo, prioridad, fecha_limite, estado) VALUES
('Terminar documentación de análisis y diseño', 1, 1, 'Alta', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Pendiente'),
('Revisar pendientes del proyecto', NULL, 1, 'Media', NULL, 'Pendiente'),
('Preparar evidencia de pruebas', 2, NULL, 'Media', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'En progreso');
