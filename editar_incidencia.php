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

            header("Location: index.php?msg=" . urlencode("Incidencia actualizada correctamente."));
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
    <title>Editar Incidencia</title>
</head>

<body>
    <h1>Editar Incidencia #<?= (int)$incidencia['id'] ?></h1>
    <p><a href="index.php">← Volver al listado</a></p>

    <?php if (!empty($errores)): ?>
        <ul style="color: red;">
            <?php foreach ($errores as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Título:<br>
                <input type="text" name="titulo" value="<?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?>" required></label>
        </p>
        <p>
            <label>Descripción:<br>
                <textarea name="descripcion" rows="4" cols="40"><?= htmlspecialchars($incidencia['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
        </p>
        <p>
            <label>Prioridad:<br>
                <select name="prioridad">
                    <option value="Baja" <?= $incidencia['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
                    <option value="Media" <?= $incidencia['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
                    <option value="Alta" <?= $incidencia['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
                </select></label>
        </p>
        <p>
            <label>Email del Cliente:<br>
                <input type="text" name="email_cliente" value="<?= htmlspecialchars($incidencia['email_cliente'], ENT_QUOTES, 'UTF-8') ?>" required></label>
        </p>
        <button type="submit">Guardar Incidencia</button>
    </form>
</body>

</html>
