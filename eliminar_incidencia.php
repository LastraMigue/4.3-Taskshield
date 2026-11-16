<?php
require_once _DIR_ . '/includes/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php?msg=" . urlencode("ID inválido para eliminar."));
    exit;
}

try {
    // Siempre incluir cláusula WHERE en DELETE
    $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Verificar filas afectadas mediante rowCount()
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