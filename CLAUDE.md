# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the Application

This is a pure PHP application with no build step. To run it locally:

1. Import the database schema: `database/script_base_datos.sql` into MySQL/MariaDB.
2. Update credentials in `config/conexion.php` if needed.
3. Serve the project root with a PHP-capable web server (e.g., XAMPP, Laragon, or `php -S localhost:8000`).
4. Open `index.php` in a browser.

No Composer, npm, or package manager is used.

## Architecture

Traditional PHP with no MVC framework. Each module is a flat directory of action scripts.

### Module Structure

- `responsables/` — CRUD for people/responsible parties
- `tareas/` — CRUD for tasks, plus `cambiar_estado.php` for state transitions

Each module follows the same pattern:
- `index.php` — lists records, renders table with action links
- `crear.php` — handles GET (show form) and POST (insert + redirect)
- `editar.php` — handles GET (load record, show form) and POST (update + redirect)
- `eliminar.php` — handles GET (delete + redirect)
- `cambiar_estado.php` (tareas only) — validates and applies state transitions via stored procedure

### Shared Components

- `config/conexion.php` — returns a PDO connection; called at the top of every action file
- `includes/header.php` — Bootstrap 5.3.3 navbar, renders `?msg=` and `?error=` query params as alerts
- `includes/footer.php` — Bootstrap JS bundle

### Message / Error Flow

After a POST or action, scripts redirect to the module's `index.php` with either `?msg=<success>` or `?error=<message>` appended to the URL. `header.php` renders these automatically.

### Database

- Engine: MySQL/MariaDB
- Connection: PDO with `utf8mb4`, exceptions enabled (`PDO::ERRMODE_EXCEPTION`)
- Credentials are hardcoded in `config/conexion.php` (host `138.59.135.33`, db `control_tareas`, user `Deprabados`)

**Key tables:** `responsable`, `tarea`, `grupo_tarea`

**Database objects to be aware of:**
- View `v_tareas_detalle` — join of `tarea`, `responsable`, and `grupo_tarea`; used in task listings
- Triggers `trg_tarea_bi` / `trg_tarea_bu` — auto-set/clear `fecha_finalizacion` when `estado` changes to/from `'Finalizada'`
- Stored procedure `sp_cambiar_estado_tarea(id, new_estado, OUT result)` — validates allowed transitions before updating; called by `tareas/cambiar_estado.php`

**Valid task state transitions:**
- Pendiente ↔ En progreso
- En progreso ↔ Bloqueada
- En progreso → Finalizada
- Finalizada → Pendiente (reactivation)

### Task Listing Sort Order

`tareas/index.php` sorts tasks so completed tasks appear last, then orders by `fecha_limite` ascending — this logic is embedded in the SQL query in that file.
