<?php
/**
 * =================================
 * ELIMINACIÓN DE INCIDENCIA
 * =================================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar identificador recibido por la URL ($_GET)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php?msg=" . urlencode("ID de incidencia no válido."));
    exit;
}

// Consultar técnico asignado y estado actual antes de eliminar
$stmtInc = $pdo->prepare("SELECT tecnico_id, estado FROM incidencias WHERE id = :id");
$stmtInc->execute([':id' => $id]);
$inc = $stmtInc->fetch(PDO::FETCH_ASSOC);

if ($inc) {
    // Si tenía técnico asignado y no estaba resuelta, descontar la tarea activa
    if (!empty($inc['tecnico_id']) && $inc['estado'] !== 'Resuelta') {
        $pdo->prepare("UPDATE tecnicos SET tareas_activas = GREATEST(0, tareas_activas - 1) WHERE id = :id")
            ->execute([':id' => $inc['tecnico_id']]);
    }

    // Eliminar la incidencia usando sentencia preparada con cláusula WHERE
    $stmtDel = $pdo->prepare("DELETE FROM incidencias WHERE id = :id");
    $stmtDel->execute([':id' => $id]);

    // Verificar con rowCount() que el borrado fue exitoso
    $mensaje = $stmtDel->rowCount() > 0 
        ? "Incidencia #$id eliminada correctamente." 
        : "No se pudo eliminar la incidencia #$id.";
} else {
    $mensaje = "No se encontró la incidencia #$id o ya había sido eliminada.";
}

// Redirigir al panel principal con el mensaje resultante
header("Location: index.php?msg=" . urlencode($mensaje));
exit;
