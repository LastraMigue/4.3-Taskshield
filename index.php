<?php
/**
 * =========================================================================
 * PANEL PRINCIPAL DE INCIDENCIAS (index.php)
 * =========================================================================
 * Requisitos cumplidos:
 * 1. Conexión segura con PDO mediante la función getConexion().
 * 2. Consulta SELECT con LEFT JOIN para vincular técnicos e incidencias.
 * 3. Sentencia preparada segura ($pdo->prepare).
 * 4. Obtención de datos obligatoria con fetchAll(PDO::FETCH_ASSOC).
 * 5. Bucle foreach integrado limpiamente en HTML y protección contra XSS.
 * =========================================================================
 */

require_once __DIR__ . '/conexion.php';

$incidencias = [];
$errorCarga = null;

try {
    $pdo = getConexion();

    // Consulta con LEFT JOIN para obtener el nombre del técnico si está asignado
    $sql = "SELECT i.id, i.titulo, i.descripcion, i.prioridad, i.email_cliente, 
                   i.estado, i.fecha_creacion, t.nombre AS tecnico_nombre
            FROM incidencias i
            LEFT JOIN tecnicos t ON i.tecnico_id = t.id
            ORDER BY i.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Recuperamos todos los registros como arrays asociativos
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorCarga = "No se pudieron cargar las incidencias: " . $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Incidencias de soporte</h1>
        <p class="help-text">Listado completo de averías y tareas registradas en la intranet.</p>
    </div>
    <div>
        <a href="nueva_incidencia.php" class="btn btn-primary">+ Registrar una nueva incidencia</a>
    </div>
</div>

<?php if ($errorCarga): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($errorCarga, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card">
    <?php if (empty($incidencias)): ?>
        <p class="help-text" style="text-align: center; padding: 2rem 0;">
            No hay incidencias registradas en este momento.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Prioridad</th>
                        <th>Email Cliente</th>
                        <th>Estado</th>
                        <th>Técnico Asignado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencias as $incidencia):
                        // Clases dinámicas para badges
                        $clasePrioridad = 'badge-prioridad-' . strtolower($incidencia['prioridad']);
                        $claseEstado = 'badge-estado-' . str_replace(' ', '-', strtolower($incidencia['estado']));
                        // Descripción recortada de forma segura para caracteres UTF-8
                        $descCorta = mb_strlen($incidencia['descripcion']) > 55 ? mb_substr($incidencia['descripcion'], 0, 55) . '...' : $incidencia['descripcion'];
                        ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($incidencia['id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (!empty($descCorta)): ?>
                                    <br><small class="help-text"><?= htmlspecialchars($descCorta, ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $clasePrioridad ?>">
                                    <?= htmlspecialchars($incidencia['prioridad'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($incidencia['email_cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $claseEstado ?>">
                                    <?= htmlspecialchars($incidencia['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($incidencia['tecnico_nombre'])): ?>
                                    <?= htmlspecialchars($incidencia['tecnico_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($incidencia['fecha_creacion'])), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Asignar técnico (Módulo de transacciones) -->
                                    <a href="asignar_incidencia.php?id=<?= urlencode($incidencia['id']) ?>"
                                        class="btn btn-warning btn-sm" title="Asignar técnico con transacción">
                                        Asignar
                                    </a>
                                    <!-- Modificar estado -->
                                    <a href="editar_incidencia.php?id=<?= urlencode($incidencia['id']) ?>"
                                        class="btn btn-secondary btn-sm" title="Editar estado">
                                        Editar
                                    </a>
                                    <!-- Eliminar con confirmación -->
                                    <a href="eliminar_incidencia.php?id=<?= urlencode($incidencia['id']) ?>"
                                        class="btn btn-danger btn-sm" title="Eliminar incidencia"
                                        onclick="return confirm('¿Confirmas que deseas eliminar la incidencia #<?= $incidencia['id'] ?>?');">
                                        Borrar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>