# Casos de Prueba - Intranet de Soporte IT

| Prueba | ¿Qué se prueba? | Datos introducidos | Resultado esperado | ¿Qué ocurrió? | Estado |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **CP-01** | **Email incorrecto en alta** (`nueva_incidencia.php`) | Email: `correo-sin-arroba` | Salta el filtro de email, no se guarda nada en la base de datos y avisa del error. | El sistema bloqueó la inserción y mostró el mensaje: *"El formato del correo electrónico no es válido."* | **OK** |
| **CP-02** | **Editar incidencia que no existe** (`editar_incidencia.php`) | URL: `editar_incidencia.php?id=9999` | Detecta que el ID no existe, devuelve error 404 y no muestra el formulario. | Se detuvo la carga con el mensaje: *"Error: La incidencia solicitada no existe."* y código HTTP 404. | **OK** |
| **CP-03** | **Fallo en la transacción al asignar** (`asignar_tecnico.php`) | Incidencia ID: `2`<br>Técnico ID: `999` (no existe) | Falla al buscar el técnico, se cancela la transacción (`rollBack`) y la incidencia queda intacta. | Se canceló la operación y se comprobó en la base de datos que la incidencia siguió en estado `Pendiente` y sin técnico. | **OK** |

---

## Cómo probar cada caso de forma sencilla

### 1. Probar email incorrecto (CP-01)
1. Entra en **Registrar Nueva Incidencia**.
2. Escribe en el campo de email un texto cualquiera sin arroba (por ejemplo: `minombre`).
3. Pulsa **Guardar Incidencia**: el sistema no guardará nada y mostrará el aviso de formato incorrecto.

### 2. Probar ID que no existe (CP-02)
1. En la barra de direcciones del navegador escribe directamente:
   ```text
   http://localhost/Act_servidor/4.3-Taskshield/editar_incidencia.php?id=9999
   ```
2. Pulsa Enter: la página no cargará ningún formulario y mostrará el mensaje de "Error: La incidencia solicitada no existe."

### 3. Probar fallo en la transacción (CP-03)
1. En el panel principal (`index.php`), haz clic derecho en el desplegable de técnicos de cualquier incidencia y pulsa **Inspeccionar**.
2. En el código HTML que aparece, cambia un valor `value="1"` por `value="999"`.
3. Elige esa opción en el desplegable: el sistema detectará que el técnico no existe, cancelará la operación con `rollBack()` y comprobarás que la incidencia no se modificó.
