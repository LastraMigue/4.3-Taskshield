<?php
/**
 * ===============================
 * CONEXIÓN A LA BASE DE DATOS
 * ===============================
 */

require_once __DIR__ . '/../config/config.php';

try {
    // Configuración segura del driver PDO
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones ante errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Array asociativo por defecto
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactivar emulación de preparadas
    ];

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opciones);

} catch (PDOException $e) {
    // Registro interno del error y mensaje genérico al usuario
    error_log('[BD Error Conexión] ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor. No se pudo conectar con la base de datos.');
}
