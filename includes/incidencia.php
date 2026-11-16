<?php
/**
 * ===============================
 * MODELO DE ENTIDAD INCIDENCIA
 * ===============================
 */

class Incidencia
{
    public int $id;
    public string $titulo;
    public ?string $descripcion;
    public string $prioridad;
    public string $estado;
    public string $email_cliente;
    public ?int $tecnico_id;
    public ?string $fecha_creacion;
    public ?string $tecnico;
}
