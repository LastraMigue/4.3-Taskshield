<?php
/**
 * ===============================
 * PANEL PRINCIPAL DE INCIDENCIAS
 * ===============================
 */

require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/incidencia.php';

// Consulta de incidencias activas junto al técnico asignado
$sql = "SELECT i.id, i.titulo, i.descripcion, i.prioridad, i.estado, i.email_cliente, i.tecnico_id, i.fecha_creacion, t.nombre AS tecnico
        FROM incidencias i
        LEFT JOIN tecnicos t ON i.tecnico_id = t.id
        WHERE i.estado != :estado_excluido
        ORDER BY i.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':estado_excluido' => 'Resuelta']);

// Mapeo de filas a objetos de la clase Incidencia
$incidencias = $stmt->fetchAll(PDO::FETCH_CLASS, 'Incidencia');

// Lista de técnicos para el selector de asignación directa
$tecnicos = $pdo->query("SELECT id, nombre, tareas_activas FROM tecnicos")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet Soporte IT - TaskShield</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
            <div>
                <h1>Panel de Incidencias Activas</h1>
                <p class="subtitle">Gestión y asignación de incidencias del departamento de soporte.</p>
            </div>
            <div>
                <a href="nueva_incidencia.php" class="btn btn-primary">+ Registrar Nueva Incidencia</a>
            </div>
        </header>

        <?php if (isset($_GET['creada'])): ?>
            <div class="msg">Incidencia creada correctamente con ID #<?= htmlspecialchars($_GET['creada'], ENT_QUOTES, 'UTF-8') ?>.</div>
        <?php elseif (isset($_GET['msg'])): ?>
            <div class="msg"><?= htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Técnico Asignado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incidencias)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                No hay incidencias activas registradas en este momento.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($incidencias as $inc): ?>
                            <tr>
                                <td><strong>#<?= (int)$inc->id ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($inc->titulo, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($inc->descripcion)): ?>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars(mb_substr($inc->descripcion, 0, 55), ENT_QUOTES, 'UTF-8') . (mb_strlen($inc->descripcion) > 55 ? '...' : '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-prioridad-<?= strtolower($inc->prioridad) ?>">
                                        <?= htmlspecialchars($inc->prioridad, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-estado-<?= str_replace(' ', '-', strtolower($inc->estado)) ?>">
                                        <?= htmlspecialchars($inc->estado, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($inc->email_cliente, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="POST" action="asignar_tecnico.php" style="margin: 0;">
                                        <input type="hidden" name="id" value="<?= (int)$inc->id ?>">
                                        <select name="tecnico_id" onchange="this.form.submit()">
                                            <option value="">-- Asignar técnico --</option>
                                            <?php foreach ($tecnicos as $tec): ?>
                                                <option value="<?= (int)$tec['id'] ?>" <?= (int)$inc->tecnico_id === (int)$tec['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tec['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$tec['tareas_activas'] ?> tareas)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="editar_incidencia.php?id=<?= (int)$inc->id ?>">Editar</a>
                                        <a href="eliminar_incidencia.php?id=<?= (int)$inc->id ?>" 
                                           onclick="return confirm('¿Seguro que deseas eliminar la incidencia #<?= (int)$inc->id ?>?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
