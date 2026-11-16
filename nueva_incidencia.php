<?php
/**
 * ===============================
 * REGISTRO DE NUEVA INCIDENCIA
 * ===============================
 */

require_once __DIR__ . '/includes/conexion.php';

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

    // Inserción en la base de datos
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO incidencias (titulo, descripcion, prioridad, email_cliente) 
                    VALUES (:titulo, :descripcion, :prioridad, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo'      => $titulo,
                ':descripcion' => $descripcion,
                ':prioridad'   => $prioridad,
                ':email'       => $email
            ]);

            $nuevoId = $pdo->lastInsertId();
            header("Location: index.php?creada=" . urlencode($nuevoId));
            exit;

        } catch (PDOException $e) {
            error_log('[Error INSERT Incidencia] ' . $e->getMessage());
            $errores[] = 'Error interno al guardar la incidencia.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Incidencia - TaskShield</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 650px;">
        <div class="card">
            <a href="index.php" class="back-link">← Volver al listado</a>
            <h1>Registrar Nueva Incidencia</h1>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Introduce los detalles de la incidencia técnica.</p>

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
                    <input type="text" id="titulo" name="titulo" value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Ej. Bloqueo en pasarela de pago" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción detallada:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe los síntomas y pasos para reproducir..."><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="prioridad">Prioridad:</label>
                    <select id="prioridad" name="prioridad">
                        <option value="Baja" <?= (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Baja') ? 'selected' : '' ?>>Baja</option>
                        <option value="Media" <?= (!isset($_POST['prioridad']) || $_POST['prioridad'] === 'Media') ? 'selected' : '' ?>>Media</option>
                        <option value="Alta" <?= (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Alta') ? 'selected' : '' ?>>Alta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email_cliente">Email del Cliente:</label>
                    <input type="email" id="email_cliente" name="email_cliente" value="<?= isset($_POST['email_cliente']) ? htmlspecialchars($_POST['email_cliente'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="cliente@empresa.com" required>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Incidencia</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
