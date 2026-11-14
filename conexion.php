<?php
/**
 * ================================================================================================================
 * ARCHIVO DE CONEXIÓN A LA BASE DE DATOS (conexion.php)Archivo creado el día 13/11/2026
 * ================================================================================================================
 * Este archivo crea e inicializa la conexión con MySQL utilizando la clase PDO
 * 
 * Requisitos cumplidos: 
 * 1. Uso obligatorio de excepciones: PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
 * 2. Desactivación de emulación de preparadas: PDO::ATTR_EMULATE_PREPARES => false
 * (obliga a MySQL a compilar sentencias preparadas reales, blindando la app
 * frente a inyecciones SQL). 
 * 3. Modo de obtención predeterminado: PDO::FETCH_ASSOC
 * 4. Patrón Singleton ligero con variables estática para reutilizar la conexión.
 */

require_once __DIR__ . '/config.php';

/**
 * Obtiene la instancia activa de la conexión PDO (reutiliza la misma conexión).
 * 
 * @return PDO Objeto de conexión configurado de forma segura. 
 */
function getConexion(): PDO
{
    //Variable estática para almacenar la instancia y evitar conexiones repetidas
    static $pdo = null;

    if ($pdo === null) {
        $dns = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $opciones = [
            //1. Lanzar excepciones cjuando ocurra un error SQL
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            //2. Desactivar sentencias preparadas emuladas (preparadas nativas en MySQL)
            PDO::ATTR_EMULATE_PREPARES => false,

            //3. Devolver los registros como arrays asociativos
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        try {
            $pdo = new PDO($dns, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die("Error crítico al conectar con la base de datos: " . $e->getMessage());
        }
    }
    return $pdo;
}