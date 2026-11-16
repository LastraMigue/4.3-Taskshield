<?php
/**
 * ===============================
 * EDICIÓN DE INCIDENCIA
 * ===============================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar identificador recibido por la URL ($_GET)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('ID de incidencia no válido.');
}

// Verificar que el registro existe en la base de datos
$stmt = $pdo->prepare("SELECT id, titulo, estado, tecnico_id FROM incidencias WHERE id = :id");
$stmt->execute([':id' => $id]);
$incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el registro no existe, devolver código HTTP 404 Not Found
if (!$incidencia) {
    http_response_code(404);
    exit('Error: La incidencia solicitada no existe.');
}

// Procesar actualización del estado
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar el estado recibido
    $nuevoEstado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
    $estadosValidos = ['Pendiente', 'En Proceso', 'Resuelta'];

    if (in_array($nuevoEstado, $estadosValidos, true)) {
        // Actualizar el estado de la incidencia mediante sentencia preparada
        $stmtUpd = $pdo->prepare("UPDATE incidencias SET estado = :estado WHERE id = :id");
        $stmtUpd->execute([
            ':estado' => $nuevoEstado,
            ':id'     => $id
        ]);

        // Si se completa (Resuelta) y tenía técnico, restar su tarea activa
        if ($incidencia['estado'] !== 'Resuelta' && $nuevoEstado === 'Resuelta' && !empty($incidencia['tecnico_id'])) {
            $pdo->prepare("UPDATE tecnicos SET tareas_activas = GREATEST(0, tareas_activas - 1) WHERE id = :id")
                ->execute([':id' => $incidencia['tecnico_id']]);
        }
        // Si se reabre desde Resuelta y tenía técnico, volver a sumar la tarea activa
        elseif ($incidencia['estado'] === 'Resuelta' && $nuevoEstado !== 'Resuelta' && !empty($incidencia['tecnico_id'])) {
            $pdo->prepare("UPDATE tecnicos SET tareas_activas = tareas_activas + 1 WHERE id = :id")
                ->execute([':id' => $incidencia['tecnico_id']]);
        }

        // Redirigir al panel principal con aviso de confirmación
        header("Location: index.php?msg=" . urlencode("Estado de la incidencia #$id actualizado a $nuevoEstado."));
        exit;
    } else {
        $error = 'El estado seleccionado no es válido.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estado - TaskShield</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 550px;">
        <div class="card">
            <a href="index.php" class="back-link">← Volver al listado</a>
            <h1>Editar Estado - Incidencia #<?= (int)$incidencia['id'] ?></h1>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                <strong>Incidencia:</strong> <?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <!-- Mensaje de error de validación -->
            <?php if (!empty($error)): ?>
                <ul class="error-list">
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
            <?php endif; ?>

            <!-- Formulario para modificar únicamente el estado -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="estado">Estado de la incidencia:</label>
                    <select id="estado" name="estado">
                        <?php foreach (['Pendiente', 'En Proceso', 'Resuelta'] as $est): ?>
                            <option value="<?= $est ?>" <?= $incidencia['estado'] === $est ? 'selected' : '' ?>>
                                <?= $est ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
