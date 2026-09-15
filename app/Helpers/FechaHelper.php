<?php
// app/Helpers/FechaHelper.php
// Helper para manejar fechas en zona horaria de Venezuela

namespace App\Pirotecnicafenix\Helpers;

/**
 * Helper para manejo de fechas en zona horaria de Venezuela
 */
class FechaHelper {
    
    /**
     * Configura la zona horaria de Venezuela (America/Caracas)
     * Esta función ya debería estar llamada desde timezone.php, pero se incluye por seguridad
     */
    private static function configurarZonaHoraria() {
        if (date_default_timezone_get() !== 'America/Caracas') {
            date_default_timezone_set('America/Caracas');
        }
    }
    
    /**
     * Obtiene la fecha y hora actual de Venezuela formateada
     * 
     * @param string $formato Formato de fecha (por defecto: Y-m-d H:i:s)
     * @return string Fecha y hora actual en formato especificado
     */
    public static function ahora($formato = 'Y-m-d H:i:s') {
        self::configurarZonaHoraria();
        return date($formato);
    }
    
    /**
     * Obtiene la fecha actual de Venezuela para campos datetime-local
     * Formato: Y-m-d\TH:i
     * 
     * @return string Fecha en formato para input datetime-local
     */
    public static function paraDateTimeLocal() {
        self::configurarZonaHoraria();
        return date('Y-m-d\TH:i');
    }
    
    /**
     * Obtiene la fecha actual de Venezuela para campos date
     * Formato: Y-m-d
     * 
     * @return string Fecha en formato para input date
     */
    public static function paraDate() {
        self::configurarZonaHoraria();
        return date('Y-m-d');
    }
    
    /**
     * Obtiene la hora actual de Venezuela para campos time
     * Formato: H:i
     * 
     * @return string Hora en formato para input time
     */
    public static function paraTime() {
        self::configurarZonaHoraria();
        return date('H:i');
    }
    
    /**
     * Formatea una fecha de base de datos (YYYY-MM-DD) a formato venezolano (DD/MM/YYYY)
     * 
     * @param string $fechaBD Fecha en formato de base de datos (YYYY-MM-DD)
     * @return string Fecha formateada (DD/MM/YYYY) o cadena vacía si no hay fecha
     */
    public static function formatoVenezolano($fechaBD) {
        if (empty($fechaBD) || $fechaBD === '0000-00-00') {
            return '';
        }
        
        self::configurarZonaHoraria();
        $timestamp = strtotime($fechaBD);
        if ($timestamp === false) {
            return $fechaBD; // Devuelve la fecha original si no se puede parsear
        }
        
        return date('d/m/Y', $timestamp);
    }
    
    /**
     * Formatea una fecha y hora de base de datos a formato venezolano (DD/MM/YYYY HH:MM)
     * 
     * @param string $fechaHoraBD Fecha y hora en formato de base de datos
     * @return string Fecha y hora formateada (DD/MM/YYYY HH:MM) o cadena vacía
     */
    public static function formatoVenezolanoConHora($fechaHoraBD) {
        if (empty($fechaHoraBD) || $fechaHoraBD === '0000-00-00 00:00:00') {
            return '';
        }
        
        self::configurarZonaHoraria();
        $timestamp = strtotime($fechaHoraBD);
        if ($timestamp === false) {
            return $fechaHoraBD; // Devuelve la fecha original si no se puede parsear
        }
        
        return date('d/m/Y H:i', $timestamp);
    }
    
    /**
     * Formatea una fecha y hora de base de datos a formato venezolano completo (DD/MM/YYYY HH:MM:SS)
     * 
     * @param string $fechaHoraBD Fecha y hora en formato de base de datos
     * @return string Fecha y hora formateada (DD/MM/YYYY HH:MM:SS) o cadena vacía
     */
    public static function formatoVenezolanoCompleto($fechaHoraBD) {
        if (empty($fechaHoraBD) || $fechaHoraBD === '0000-00-00 00:00:00') {
            return '';
        }
        
        self::configurarZonaHoraria();
        $timestamp = strtotime($fechaHoraBD);
        if ($timestamp === false) {
            return $fechaHoraBD; // Devuelve la fecha original si no se puede parsear
        }
        
        return date('d/m/Y H:i:s', $timestamp);
    }
    
    /**
     * Obtiene la fecha de hace X años (útil para fechas de nacimiento por defecto)
     * 
     * @param int $anios Número de años atrás
     * @param string $formato Formato de salida (por defecto: Y-m-d)
     * @return string Fecha hace X años
     */
    public static function haceAnios($anios, $formato = 'Y-m-d') {
        self::configurarZonaHoraria();
        return date($formato, strtotime("-$anios years"));
    }
    
    /**
     * Convierte fecha venezolana (DD/MM/YYYY) a formato de base de datos (YYYY-MM-DD)
     * 
     * @param string $fechaVenezolana Fecha en formato venezolano (DD/MM/YYYY)
     * @return string Fecha en formato de base de datos (YYYY-MM-DD) o null si inválida
     */
    public static function aFormatoBD($fechaVenezolana) {
        if (empty($fechaVenezolana)) {
            return null;
        }
        
        // Parsear fecha DD/MM/YYYY
        $partes = explode('/', $fechaVenezolana);
        if (count($partes) !== 3) {
            return null;
        }
        
        $dia = intval($partes[0]);
        $mes = intval($partes[1]);
        $anio = intval($partes[2]);
        
        if (!checkdate($mes, $dia, $anio)) {
            return null;
        }
        
        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    }
    
    /**
     * Obtiene el año actual de Venezuela
     * Útil para copyright en el footer
     * 
     * @return string Año actual
     */
    public static function anioActual() {
        self::configurarZonaHoraria();
        return date('Y');
    }
    
    /**
     * Obtiene el nombre del mes en español
     * 
     * @param int $mes Número del mes (1-12)
     * @return string Nombre del mes en español
     */
    public static function nombreMes($mes) {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $meses[$mes] ?? 'Desconocido';
    }
}
?>