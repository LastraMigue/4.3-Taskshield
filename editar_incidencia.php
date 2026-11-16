<?php
/**
 * ===============================
 * EDICIÓN DE INCIDENCIA
 * ===============================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar identificador recibido por la URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    exit('ID de incidencia no válido.');
}

// Verificar existencia del registro y recuperar datos
$stmt = $pdo->prepare("SELECT id, titulo, descripcion, prioridad, email_cliente FROM incidencias WHERE id = :id");
$stmt->execute([':id' => $id]);
$incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incidencia) {
    http_response_code(404);
    exit('Error: La incidencia solicitada no existe.');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar y sanitizar entradas
    $titulo      = trim((string) filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
    $descripcion = trim((string) filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
    $prioridad   = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
    $email       = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);

    // Validación de campos obligatorios y formatos
    if ($titulo === '') {
        $errores[] = 'El título es obligatorio.';
    }
    if (!in_array($prioridad, ['Baja', 'Media', 'Alta'], true)) {
        $errores[] = 'La prioridad seleccionada no es válida.';
    }
    if (!$email) {
        $errores[] = 'El formato del correo electrónico no es válido.';
    }

    // Actualización de la incidencia
    if (empty($errores)) {
        try {
            $sql = "UPDATE incidencias 
                    SET titulo = :titulo, descripcion = :descripcion, prioridad = :prioridad, email_cliente = :email 
                    WHERE id = :id";
            $stmtUpd = $pdo->prepare($sql);
            $stmtUpd->execute([
                ':titulo'      => $titulo,
                ':descripcion' => $descripcion,
                ':prioridad'   => $prioridad,
                ':email'       => $email,
                ':id'          => $id
            ]);

            header("Location: index.php?msg=" . urlencode("Incidencia #$id actualizada correctamente."));
            exit;

        } catch (PDOException $e) {
            error_log('[Error UPDATE Incidencia] ' . $e->getMessage());
            $errores[] = 'Error interno al guardar la incidencia.';
        }
    } else {
        // Mantener valores enviados si hay errores de validación
        $incidencia['titulo']        = $titulo;
        $incidencia['descripcion']   = $descripcion;
        $incidencia['prioridad']     = $prioridad;
        $incidencia['email_cliente'] = filter_input(INPUT_POST, 'email_cliente', FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Incidencia #<?= (int)$incidencia['id'] ?> - TaskShield</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 650px;">
        <div class="card">
            <a href="index.php" class="back-link">← Volver al listado</a>
            <h1>Editar Incidencia #<?= (int)$incidencia['id'] ?></h1>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Modifica los campos necesarios de la incidencia.</p>

            <?php if (!empty($errores)): ?>
                <ul class="error-list">
                    <?php foreach ($errores as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="titulo">Título de la incidencia:</label>
                    <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($incidencia['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="prioridad">Prioridad:</label>
                    <select id="prioridad" name="prioridad">
                        <option value="Baja" <?= $incidencia['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
                        <option value="Media" <?= $incidencia['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
                        <option value="Alta" <?= $incidencia['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email_cliente">Email del Cliente:</label>
                    <input type="email" id="email_cliente" name="email_cliente" value="<?= htmlspecialchars($incidencia['email_cliente'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
