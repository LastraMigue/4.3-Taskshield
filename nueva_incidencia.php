<?php
require_once _DIR_ . '/includes/conexion.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recuperar, validar y sanitizar con filter_input
    $titulo = trim((string) filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
    $descripcion = trim((string) filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
    $prioridad = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);

    // 2. Validar reglas de negocio
    if ($titulo === '') {
        $errores[] = 'El título es obligatorio.';
    }
    if (!in_array($prioridad, ['Baja', 'Media', 'Alta'], true)) {
        $errores[] = 'La prioridad seleccionada no es válida.';
    }
    if (!$email) {
        $errores[] = 'El formato del correo electrónico no es válido.';
    }

    // 3. Insertar en BD si todo es correcto
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO incidencias (titulo, descripcion, prioridad, email_cliente) 
                    VALUES (:titulo, :descripcion, :prioridad, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':descripcion' => $descripcion,
                ':prioridad' => $prioridad,
                ':email' => $email
            ]);

            $nuevoId = $pdo->lastInsertId(); // Recupera ID autoincremental
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
    <title>Nueva Incidencia</title>
</head>

<body>
    <h1>Registrar Nueva Incidencia</h1>
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
                <input type="text" name="titulo" required></label>
        </p>
        <p>
            <label>Descripción:<br>
                <textarea name="descripcion" rows="4" cols="40"></textarea></label>
        </p>
        <p>
            <label>Prioridad:<br>
                <select name="prioridad">
                    <option value="Baja">Baja</option>
                    <option value="Media" selected>Media</option>
                    <option value="Alta">Alta</option>
                </select></label>
        </p>
        <p>
            <label>Email del Cliente:<br>
                <input type="text" name="email_cliente" required></label>
        </p>
        <button type="submit">Guardar Incidencia</button>
    </form>
</body>

</html>