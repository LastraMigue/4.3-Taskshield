<?php
/**
 * =================================
 * ELIMINACIÓN DE INCIDENCIA
 * =================================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar identificador recibido por la URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php?msg=" . urlencode("ID inválido para eliminar."));
    exit;
}

try {
    // Eliminar incidencia con cláusula WHERE
    $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Comprobar filas afectadas
    if ($stmt->rowCount() > 0) {
        $mensaje = "Incidencia #$id eliminada correctamente.";
    } else {
        $mensaje = "No se encontró la incidencia #$id o ya había sido borrada.";
    }

    header("Location: index.php?msg=" . urlencode($mensaje));
    exit;

} catch (PDOException $e) {
    error_log('[Error DELETE Incidencia] ' . $e->getMessage());
    header("Location: index.php?msg=" . urlencode("Error técnico al intentar eliminar."));
    exit;
}
