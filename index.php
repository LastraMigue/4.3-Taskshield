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
    <title>Intranet Soporte IT</title>
</head>
<body>
    <h1>Panel de Incidencias Activas</h1>
    <p><a href="nueva_incidencia.php">+ Registrar Nueva Incidencia</a></p>

    <?php if (isset($_GET['creada'])): ?>
        <div class="msg">Incidencia creada correctamente con ID #<?= htmlspecialchars($_GET['creada'], ENT_QUOTES, 'UTF-8') ?>.</div>
    <?php elseif (isset($_GET['msg'])): ?>
        <div class="msg"><?= htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

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
                <tr><td colspan="7">No hay incidencias activas registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($incidencias as $inc): ?>
                    <tr>
                        <td><?= (int)$inc->id ?></td>
                        <td><?= htmlspecialchars($inc->titulo, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($inc->prioridad, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><strong><?= htmlspecialchars($inc->estado, ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><?= htmlspecialchars($inc->email_cliente, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="POST" action="asignar_tecnico.php" style="margin: 0;">
                                <input type="hidden" name="id" value="<?= (int)$inc->id ?>">
                                <select name="tecnico_id" onchange="this.form.submit()">
                                    <option value="">-- Asignar técnico --</option>
                                    <?php foreach ($tecnicos as $tec): ?>
                                        <option value="<?= (int)$tec['id'] ?>" <?= (int)$inc->tecnico_id === (int)$tec['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tec['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="editar_incidencia.php?id=<?= (int)$inc->id ?>">Editar</a> |
                            <a href="eliminar_incidencia.php?id=<?= (int)$inc->id ?>" 
                               onclick="return confirm('¿Seguro que deseas eliminar la incidencia #<?= (int)$inc->id ?>?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
