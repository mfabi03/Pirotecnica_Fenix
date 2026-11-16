<?php
// app/Interfaces/InterfacesCrudModel.php
namespace App\Pirotecnicafenix\Interfaces;

interface InterfacesCrudModel
{
    /**
     * Obtiene todos los registros (generalmente solo activos).
     *
     * @return array Lista de registros asociativos.
     */
    public function obtenerTodos();

    /**
     * Obtiene un registro por su ID.
     *
     * @param int $id
     * @return array|null Registro asociativo o null si no existe.
     */
    public function obtenerPorId($id);

    /**
     * Crea un nuevo registro.
     *
     * @param array $datos
     * @return int|false ID generado o false si falla.
     */
    public function crear($datos);

    /**
     * Actualiza un registro existente.
     *
     * @param int   $id
     * @param array $datos
     * @return bool
     */
    public function actualizar($id, $datos);

    /**
     * Elimina (lógicamente) un registro.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar($id);
}