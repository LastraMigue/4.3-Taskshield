CREATE DATABASE IF NOT EXISTS taskshield_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taskshield_db;

CREATE TABLE tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tareas_activas INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    prioridad ENUM('Baja', 'Media', 'Alta') NOT NULL DEFAULT 'Media',
    estado ENUM('Pendiente', 'En Proceso', 'Resuelta') NOT NULL DEFAULT 'Pendiente',
    email_cliente VARCHAR(150) NOT NULL,
    tecnico_id INT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_incidencia_tecnico FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Datos de prueba: Todos los técnicos con 0 tareas activas
INSERT INTO tecnicos (nombre, tareas_activas) VALUES 
('Laura Gómez', 0),
('Carlos Ruiz', 0),
('Elena Torres', 0);

-- Datos de prueba: Ninguna incidencia tiene técnico asignado (tecnico_id = NULL)
INSERT INTO incidencias (titulo, descripcion, prioridad, estado, email_cliente, tecnico_id) VALUES 
('Fallo de conexión VPN', 'El equipo remoto no resuelve DNS interno.', 'Alta', 'Pendiente', 'usuario1@empresa.com', NULL),
('Instalación paquete Office', 'Necesario para nueva incorporación en contabilidad.', 'Baja', 'Pendiente', 'rrhh@empresa.com', NULL),
('Impresora planta 2 atascada', 'Error recurrente en bandeja principal.', 'Media', 'Pendiente', 'admin@empresa.com', NULL);