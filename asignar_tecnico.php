<?php
/**
 * =====================================
 * ASIGNACIÓN DE TÉCNICO A INCIDENCIA
 * =====================================
 */

require_once __DIR__ . '/includes/conexion.php';

// Validar identificador de incidencia (POST o GET)
$idIncidencia = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idIncidencia) {
    header("Location: index.php?msg=" . urlencode("ID de incidencia no válido."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idTecnico = filter_input(INPUT_POST, 'tecnico_id', FILTER_VALIDATE_INT);

    if (!$idTecnico) {
        header("Location: index.php?msg=" . urlencode("Debes seleccionar un técnico válido."));
        exit;
    }

    try {
        // Transacción atómica para asignación e incremento de tareas
        $pdo->beginTransaction();

        // 1. Asignar técnico y actualizar estado de la incidencia
        $sql1 = "UPDATE incidencias 
                 SET estado = 'En Proceso', tecnico_id = :tecnico_id 
                 WHERE id = :incidencia_id";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            ':tecnico_id'    => $idTecnico,
            ':incidencia_id' => $idIncidencia
        ]);

        // 2. Incrementar tareas activas del técnico
        $sql2 = "UPDATE tecnicos 
                 SET tareas_activas = tareas_activas + 1 
                 WHERE id = :tecnico_id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':tecnico_id' => $idTecnico]);

        // Verificar que el técnico existía en la base de datos
        if ($stmt2->rowCount() === 0) {
            throw new RuntimeException("El técnico con ID $idTecnico no existe.");
        }

        // Confirmar cambios
        $pdo->commit();

        header("Location: index.php?msg=" . urlencode("Incidencia asignada y contador actualizado."));
        exit;

    } catch (Throwable $e) {
        // Revertir cambios en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[Error Transacción Asignación] ' . $e->getMessage());
        header("Location: index.php?msg=" . urlencode("Error en la asignación: el técnico no existe o falló la operación. Cambios revertidos."));
        exit;
    }
}

// Redirección al panel principal si se accede fuera de POST
header("Location: index.php");
exit;
