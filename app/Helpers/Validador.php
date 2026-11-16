<?php
// app/Helpers/Validador.php
namespace App\Pirotecnicafenix\Helpers;

use Exception;

class Validador
{
   
    public static function limpiar($valor): string
    {
        if ($valor === null) return '';
        return trim((string) $valor);
    }

    public static function escape($valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function validarCedula($cedula): bool
    {
        $cedula = self::limpiar($cedula);
        return (bool) preg_match('/^[VE]?\-?\d{6,9}$/i', $cedula);
    }

    public static function validarRIF($rif): bool
    {
        $rif = self::limpiar($rif);
        return (bool) preg_match('/^[VEJPG]?\-?\d{8}\-?\d$/i', $rif);
    }

    /**
     * Valida email.
     */
    public static function validarEmail($email): bool
    {
        return filter_var(self::limpiar($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarTelefono($telefono): bool
    {
        $telefono = preg_replace('/[^0-9]/', '', self::limpiar($telefono));
        return strlen($telefono) >= 10 && strlen($telefono) <= 12;
    }

    public static function validarId($id, string $nombreCampo = 'ID'): int
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new Exception("{$nombreCampo} inválido.");
        }
        return (int) $id;
    }

    public static function requerido($valor, string $nombreCampo): string
    {
        $valor = self::limpiar($valor);
        if ($valor === '') {
            throw new Exception("El campo '{$nombreCampo}' es obligatorio.");
        }
        return $valor;
    }

    public static function numeroPositivo($valor, string $nombreCampo, float $minimo = 0): float
    {
        if (!is_numeric($valor) || (float) $valor < $minimo) {
            throw new Exception("El campo '{$nombreCampo}' debe ser un número mayor o igual a {$minimo}.");
        }
        return (float) $valor;
    }

    public static function validarFecha($fecha, string $nombreCampo, string $formato = 'Y-m-d'): string
    {
        $fecha = self::limpiar($fecha);
        $dt = \DateTime::createFromFormat($formato, $fecha);
        $errores = \DateTime::getLastErrors();
        if (!$dt || ($errores && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
            throw new Exception("La fecha '{$nombreCampo}' no es válida.");
        }
        return $fecha;
    }

    public static function validarEdad($fechaNacimiento, int $edadMinima = 18): bool
    {
        try {
            $fechaNac = new \DateTime($fechaNacimiento);
            $hoy = new \DateTime();
            if ($fechaNac > $hoy) return false;
            return $hoy->diff($fechaNac)->y >= $edadMinima;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function sanitizarArray(array $datos): array
    {
        $resultado = [];
        foreach ($datos as $clave => $valor) {
            if (is_array($valor)) {
                $resultado[$clave] = self::sanitizarArray($valor);
            } else {
                $resultado[$clave] = self::limpiar($valor);
            }
        }
        return $resultado;
    }
}