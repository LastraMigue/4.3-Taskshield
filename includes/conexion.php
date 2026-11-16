<?php
/**
 * =======================================
 * ARCHIVO DE CONEXIÓN A LA BASE DE DATOS
 * =======================================
 */

require_once __DIR__ . '/../config/config.php';

try {
    $opciones = [

        // 1. Lanza excepciones
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // 2. Array asociativo por defecto
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // 3. Prepares nativos (anti-inyección)
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opciones);

} catch (PDOException $e) {
    // Registro interno en servidor y mensaje genérico al usuario
    error_log('[BD Error Conexión] ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor. No se pudo conectar con la base de datos.');
}