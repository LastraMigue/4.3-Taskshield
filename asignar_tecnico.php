<?php
/**
 * =====================================
 * ASIGNACIÓN DE TÉCNICO A INCIDENCIA
 * =====================================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar parámetros recibidos por POST
$idIncidencia = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$idTecnico    = filter_input(INPUT_POST, 'tecnico_id', FILTER_VALIDATE_INT);

if (!$idIncidencia || !$idTecnico) {
    header("Location: index.php?msg=" . urlencode("Debes seleccionar una incidencia y un técnico válidos."));
    exit;
}

// Consultar si la incidencia ya tenía un técnico previo
$stmtAnt = $pdo->prepare("SELECT tecnico_id FROM incidencias WHERE id = :id");
$stmtAnt->execute([':id' => $idIncidencia]);
$tecAnterior = $stmtAnt->fetchColumn();

// Evitar sumar +1 si se reasigna el mismo técnico que ya tenía
if ($tecAnterior == $idTecnico) {
    header("Location: index.php?msg=" . urlencode("El técnico ya está asignado a esta incidencia."));
    exit;
}

try {
    // Iniciar transacción atómica (ambas operaciones deben cumplirse o ninguna)
    $pdo->beginTransaction();

    // 1. Asignar técnico a la incidencia y cambiar su estado a 'En Proceso'
    $sql1 = "UPDATE incidencias 
             SET estado = 'En Proceso', tecnico_id = :tecnico_id 
             WHERE id = :incidencia_id";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([
        ':tecnico_id'    => $idTecnico,
        ':incidencia_id' => $idIncidencia
    ]);

    // 2. Incrementar en +1 el contador de tareas activas del nuevo técnico
    $sql2 = "UPDATE tecnicos 
             SET tareas_activas = tareas_activas + 1 
             WHERE id = :tecnico_id";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':tecnico_id' => $idTecnico]);

    // Si el técnico no existe en la tabla, forzar excepción para revertir cambios
    if ($stmt2->rowCount() === 0) {
        throw new Exception("El técnico con ID $idTecnico no existe.");
    }

    // 3. Descontar la tarea activa al técnico previo si existía
    if ($tecAnterior) {
        $stmtRestar = $pdo->prepare("UPDATE tecnicos SET tareas_activas = GREATEST(0, tareas_activas - 1) WHERE id = :id");
        $stmtRestar->execute([':id' => $tecAnterior]);
    }

    // Confirmar operaciones en la base de datos
    $pdo->commit();

    $mensaje = "Técnico asignado correctamente e incidencia puesta En Proceso.";

} catch (Exception $e) {
    // Revertir todos los cambios si cualquiera de las operaciones falla
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[Error Transacción Asignación] ' . $e->getMessage());
    $mensaje = "Error en la asignación: técnico no válido o fallo en la operación. Cambios revertidos.";
}

// Redirigir al panel principal con el mensaje resultante
header("Location: index.php?msg=" . urlencode($mensaje));
exit;
