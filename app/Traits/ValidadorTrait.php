<?php
// app/Traits/ValidadorTrait.php

namespace App\Pirotecnicafenix\Traits;

use DateTime;
use Exception;

trait ValidadorTrait
{
    protected array $errors = [];

    // ==================================================
    // MÉTODOS BASE
    // ==================================================

    public function validateRequired(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $this->errors[$field] = "El campo '{$field}' es obligatorio.";
            }
        }
        return empty($this->errors);
    }

    public function validateMinLength(string $value, string $field, int $min): bool
    {
        if (mb_strlen(trim($value)) < $min) {
            $this->errors[$field] = "El campo '{$field}' debe tener al menos {$min} caracteres.";
            return false;
        }
        return true;
    }

    public function validateRole(string $role, array $allowed = ['admin', 'user']): bool
    {
        if (!in_array($role, $allowed, true)) {
            $this->errors['role'] = "El rol seleccionado no es válido.";
            return false;
        }
        return true;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }

    // ==================================================
    // NORMALIZACIÓN DE DOCUMENTOS
    // ==================================================

    public function soloDigitos($valor): string
    {
        return preg_replace('/\D/', '', (string)$valor);
    }

    public function normalizarDocumento(string $tipo, string $numero, ?string $digito = null): string
    {
        $tipo   = strtoupper(trim($tipo));
        $numero = $this->soloDigitos($numero);

        if (!in_array($tipo, ['V', 'E', 'J', 'P', 'G'], true)) {
            throw new Exception("Tipo de documento inválido. Use V, E, J, P o G.");
        }
        if ($numero === '') {
            throw new Exception("El número de documento es obligatorio.");
        }
        if (strlen($numero) < 6 || strlen($numero) > 9) {
            throw new Exception("El documento debe tener entre 6 y 9 dígitos.");
        }

        if ($digito !== null && $digito !== '') {
            $digito = $this->soloDigitos($digito);
            if (strlen($digito) !== 1) {
                throw new Exception("El dígito verificador debe ser un solo número.");
            }
            return "{$tipo}-{$numero}-{$digito}";
        }

        return "{$tipo}-{$numero}";
    }

    public function inferirTipoDocumento(string $numero, string $tipoPreferido = 'V'): string
    {
        $numero = $this->soloDigitos($numero);
        $tipoPreferido = strtoupper($tipoPreferido);
        if (!in_array($tipoPreferido, ['V', 'E', 'J', 'P', 'G'], true)) {
            $tipoPreferido = 'V';
        }
        if ($tipoPreferido === 'V' && strlen($numero) === 9) {
            return 'J';
        }
        return $tipoPreferido;
    }

    public function documentoCompleto(string $tipo, string $numero, ?string $digito = null): string
    {
        return $this->normalizarDocumento($tipo, $numero, $digito);
    }

    // ==================================================
    // VALIDACIONES DE DOMINIO
    // ==================================================

    public function validateCedula(string $cedula, string $field = 'cedula'): bool
    {
        $limpio = $this->soloDigitos($cedula);
        if (strlen($limpio) < 6 || strlen($limpio) > 9) {
            $this->errors[$field] = "La cédula debe tener entre 6 y 9 dígitos (sin letras).";
            return false;
        }
        return true;
    }

    public function validateRIF(string $rif, string $field = 'rif'): bool
    {
        $limpio = $this->soloDigitos($rif);
        if (strlen($limpio) < 8 || strlen($limpio) > 10) {
            $this->errors[$field] = "El RIF debe tener entre 8 y 10 dígitos (sin letras).";
            return false;
        }
        return true;
    }

    public function validateEmail(string $email, string $field = 'email'): bool
    {
        if (!filter_var($this->limpiar($email), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El email no es válido.";
            return false;
        }
        return true;
    }

    public function validateTelefono(string $telefono, string $field = 'telefono'): bool
    {
        $limpio = $this->soloDigitos($telefono);
        if (strlen($limpio) < 10 || strlen($limpio) > 12) {
            $this->errors[$field] = "El teléfono debe tener entre 10 y 12 dígitos.";
            return false;
        }
        return true;
    }

    public function validateId($id, string $field = 'id'): bool
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->errors[$field] = "El ID no es válido.";
            return false;
        }
        return true;
    }

    public function validateNumeroPositivo($valor, string $field, float $minimo = 0): bool
    {
        if (!is_numeric($valor) || (float)$valor < $minimo) {
            $this->errors[$field] = "El campo '{$field}' debe ser un número mayor o igual a {$minimo}.";
            return false;
        }
        return true;
    }

    public function validateFecha(string $fecha, string $field, string $formato = 'Y-m-d'): bool
    {
        $fecha = $this->limpiar($fecha);
        $dt = DateTime::createFromFormat($formato, $fecha);
        $err = DateTime::getLastErrors();
        if (!$dt || ($err && ($err['warning_count'] > 0 || $err['error_count'] > 0))) {
            $this->errors[$field] = "La fecha del campo '{$field}' no es válida.";
            return false;
        }
        return true;
    }

    public function validateEdad(string $fechaNacimiento, int $edadMinima = 18, string $field = 'fecha_nacimiento'): bool
    {
        try {
            $fechaNac = new DateTime($fechaNacimiento);
            $hoy = new DateTime();
            if ($fechaNac > $hoy || $hoy->diff($fechaNac)->y < $edadMinima) {
                $this->errors[$field] = "Debes tener al menos {$edadMinima} años.";
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->errors[$field] = "La fecha de nacimiento no es válida.";
            return false;
        }
    }

    // ==================================================
    // HELPERS DE SANITIZACIÓN
    // ==================================================

    public function limpiar($valor): string
    {
        if ($valor === null) return '';
        return trim((string)$valor);
    }

    public function escape($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function sanitizarArray(array $datos): array
    {
        $resultado = [];
        foreach ($datos as $clave => $valor) {
            $resultado[$clave] = is_array($valor)
                ? $this->sanitizarArray($valor)
                : $this->limpiar($valor);
        }
        return $resultado;
    }

    // ==================================================
    // NORMALIZACIÓN DE FECHA / HORA
    // ==================================================

    public function normalizarFecha($fecha, ?string $default = null): ?string
    {
        $fecha = trim((string)$fecha);
        if ($fecha === '') return $default;
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt || $dt->format('Y-m-d') !== $fecha) return $default;
        return $fecha;
    }

    public function hora_es_am_pm($hora): string
    {
        if (!$hora || $hora === '—' || $hora === '-') return '—';
        if (strpos((string)$hora, '0000-00-00') === 0) return '—';
        $ts = strtotime($hora);
        if ($ts === false) return '—';
        $h = (int)date('g', $ts);
        $m = date('i', $ts);
        $s = date('s', $ts);
        $ampm = ((int)date('G', $ts) < 12) ? 'a. m.' : 'p. m.';
        return "{$h}:{$m}:{$s} {$ampm}";
    }

    // ==================================================
    // MÉTODOS PUENTE (devuelven valor normalizado o lanzan Exception)
    // ==================================================

    public function requerido($valor, string $campo): string
    {
        $limpio = $this->limpiar($valor);
        if ($limpio === '') {
            throw new Exception("El campo '{$campo}' es obligatorio.");
        }
        return $limpio;
    }

    public function validarFechaOExcepcion(string $fecha, string $campo, string $formato = 'Y-m-d'): string
    {
        if (!$this->validateFecha($fecha, $campo, $formato)) {
            throw new Exception($this->errors[$campo]);
        }
        return $this->limpiar($fecha);
    }

    public function numeroPositivo($valor, string $campo, float $minimo = 0): float
    {
        if (!$this->validateNumeroPositivo($valor, $campo, $minimo)) {
            throw new Exception($this->errors[$campo]);
        }
        return (float)$valor;
    }

    public function validarId($id, string $campo = 'id'): int
    {
        if (!$this->validateId($id, $campo)) {
            throw new Exception($this->errors[$campo]);
        }
        return (int)$id;
    }
}