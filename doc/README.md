# Intranet de Soporte Informático - TaskShield

Este proyecto consiste en una intranet departamental de soporte técnico desarrollada en **PHP** con acceso a datos mediante **PDO** (MySQL/MariaDB), orientada a garantizar la integridad transaccional, la seguridad frente a inyecciones SQL y la prevención de vulnerabilidades XSS.

---

## 1. Arquitectura del Proyecto

La aplicación implementa una arquitectura modular que separa la configuración de credenciales, la persistencia de datos, el modelo de dominio y la capa de presentación/controladores:

```text
4.3-Taskshield/
├── config/
│   └── config.php          # Centraliza credenciales de BD y DSN (ignorado por .gitignore)
├── includes/
│   ├── conexion.php        # Instanciación segura de PDO con gestión de excepciones
│   └── incidencia.php      # Clase de dominio para mapeo de objetos (PDO::FETCH_CLASS)
├── doc/
│   ├── schema.sql          # Estructura de tablas DDL e inserciones de prueba
│   ├── casos_prueba.md     # Batería de pruebas funcionales y de seguridad ejecutadas
│   └── README.md           # Documentación técnica de arquitectura y pruebas
├── index.php               # Panel principal: listado de incidencias activas y asignación
├── nueva_incidencia.php    # Formulario de alta y validación de nuevas incidencias
├── editar_incidencia.php   # Formulario de modificación del estado de la incidencia
├── eliminar_incidencia.php # Controlador de borrado seguro con confirmación
└── asignar_tecnico.php     # Controlador de asignación con transacciones atómicas
```

### Principios de Seguridad y Buenas Prácticas Aplicados:
* **Centralización y Aislamiento de Credenciales**: Definidas mediante constantes en `config/config.php` y excluidas del control de versiones mediante `.gitignore`.
* **Configuración Estricta de PDO**:
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`: Captura de errores en bloques `try-catch` sin exponer mensajes del sistema al usuario.
  - `PDO::ATTR_EMULATE_PREPARES => false`: Uso obligatorio de sentencias preparadas nativas del motor MySQL.
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`: Formato asociativo estándar para lectura de registros.
* **Mapeo a Objetos de Dominio**: Uso de `PDO::FETCH_CLASS` en `index.php` para hidratar instancias fuertemente tipadas de la clase `Incidencia`.
* **Validación y Sanitización en Servidor**: Procesamiento de entradas mediante `filter_input()` (`FILTER_VALIDATE_EMAIL`, `FILTER_SANITIZE_SPECIAL_CHARS`, `FILTER_VALIDATE_INT`).
* **Protección contra XSS (Cross-Site Scripting)**: Escapado de salidas HTML mediante `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
* **Transacciones Atómicas (ACID)**: En `asignar_tecnico.php`, se sincroniza la actualización de la incidencia y el incremento del contador del técnico dentro de `beginTransaction()`, aplicando `rollBack()` si ocurre cualquier fallo.

---

## 2. Cómo Probar el Proyecto

### Requisitos Previos:
- Servidor local con **Apache** y **MySQL/MariaDB** (como XAMPP, Laragon o Docker).
- PHP 8.0 o superior con extensión `pdo_mysql` habilitada.

### Pasos de Instalación:
1. **Crear la Base de Datos**:
   - Abrir phpMyAdmin (o la consola de MySQL).
   - Importar el archivo `doc/schema.sql`. Esto creará la base de datos `taskshield_db`, las tablas `tecnicos` e `incidencias`, e insertará datos iniciales de prueba.
2. **Configurar Credenciales**:
   - Verificar que en `config/config.php` los parámetros (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) coincidan con tu entorno local (por defecto en XAMPP: usuario `root` sin contraseña).
3. **Acceder a la Aplicación**:
   - Abrir el navegador e ingresar a la URL:
     ```text
     http://localhost/Act_servidor/4.3-Taskshield/index.php
     ```

### Flujo de Pruebas Funcionales:
1. **Consultar Incidencias Activas**:
   - En `index.php` se visualizan únicamente las incidencias que no están en estado `Resuelta`, mostrando el nombre del técnico asociado o `Sin asignar`.
2. **Registrar Nueva Incidencia**:
   - Hacer clic en `+ Registrar Nueva Incidencia`.
   - Completar el formulario con datos válidos y pulsar `Guardar Incidencia`.
   - El sistema redirige al panel mostrando un mensaje de confirmación con el ID generado mediante `lastInsertId()`.
3. **Editar Estado de la Incidencia**:
   - En la fila de una incidencia, hacer clic en `Editar`.
   - Modificar el estado (`Pendiente`, `En Proceso` o `Resuelta`) y guardar los cambios. El sistema valida la existencia previa del ID en la base de datos.
4. **Asignación Rápida de Técnico**:
   - En la columna `Técnico Asignado`, desplegar el selector de la incidencia y elegir un técnico.
   - El formulario envía la petición automáticamente por POST a `asignar_tecnico.php`, ejecuta la transacción (cambia el estado a `En Proceso` y suma +1 tarea activa) y recarga el panel con el aviso de éxito.
5. **Eliminar Incidencia**:
   - Pulsar `Eliminar` en cualquiera de las incidencias activas.
   - Aceptar el cuadro de confirmación del navegador. El registro se elimina y `eliminar_incidencia.php` comprueba con `rowCount()` que el borrado fue efectivo.
6. **Casos Límite y Seguridad**:
   - Consultar el documento `doc/casos_prueba.md` para verificar los escenarios de email inválido, ID inexistente en URL (HTTP 404) y reversión transaccional (`rollBack`).
