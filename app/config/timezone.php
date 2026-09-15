<?php
// app/Config/timezone.php
// Configuración de zona horaria para Venezuela (America/Caracas)

/**
 * Configura la zona horaria de Venezuela en toda la aplicación
 * Venezuela usa America/Caracas (UTC-4)
 * 
 * @return void
 */
function configurarZonaHorariaVenezuela() {
    // Verificar si ya está configurada
    if (date_default_timezone_get() !== 'America/Caracas') {
        // Configurar zona horaria
        date_default_timezone_set('America/Caracas');
        
        // Para MySQL/MariaDB (si se usa la conexión)
        if (function_exists('ini_set')) {
            ini_set('date.timezone', 'America/Caracas');
        }
        
        // Log opcional para depuración
        if (defined('DEBUG') && DEBUG === true) {
            error_log('Zona horaria configurada a: America/Caracas');
        }
    }
}

/**
 * Obtiene la fecha y hora actual de Venezuela formateada
 * 
 * @param string $formato Formato de fecha (por defecto: Y-m-d H:i:s)
 * @return string Fecha y hora actual en formato especificado
 */
function fechaHoraVenezuela($formato = 'Y-m-d H:i:s') {
    configurarZonaHorariaVenezuela();
    return date($formato);
}

/**
 * Obtiene la fecha actual de Venezuela para campos datetime-local
 * Formato: Y-m-d\TH:i
 * 
 * @return string Fecha en formato para input datetime-local
 */
function fechaParaDateTimeLocal() {
    configurarZonaHorariaVenezuela();
    return date('Y-m-d\TH:i');
}

/**
 * Obtiene la fecha actual de Venezuela para campos date
 * Formato: Y-m-d
 * 
 * @return string Fecha en formato para input date
 */
function fechaParaDate() {
    configurarZonaHorariaVenezuela();
    return date('Y-m-d');
}

/**
 * Obtiene la hora actual de Venezuela para campos time
 * Formato: H:i
 * 
 * @return string Hora en formato para input time
 */
function horaParaTime() {
    configurarZonaHorariaVenezuela();
    return date('H:i');
}

/**
 * Formatea una fecha de base de datos (YYYY-MM-DD) a formato venezolano (DD/MM/YYYY)
 * Función global para uso en vistas
 * 
 * @param string $fechaBD Fecha en formato de base de datos (YYYY-MM-DD)
 * @return string Fecha formateada (DD/MM/YYYY) o cadena vacía si no hay fecha
 */
function formatoFechaVenezuela($fechaBD) {
    if (empty($fechaBD) || $fechaBD === '0000-00-00') {
        return '';
    }
    
    configurarZonaHorariaVenezuela();
    $timestamp = strtotime($fechaBD);
    if ($timestamp === false) {
        return $fechaBD; // Devuelve la fecha original si no se puede parsear
    }
    
    return date('d/m/Y', $timestamp);
}

/**
 * Formatea una fecha y hora de base de datos a formato venezolano (DD/MM/YYYY HH:MM)
 * Función global para uso en vistas
 * 
 * @param string $fechaHoraBD Fecha y hora en formato de base de datos
 * @return string Fecha y hora formateada (DD/MM/YYYY HH:MM) o cadena vacía
 */
function formatoFechaHoraVenezuela($fechaHoraBD) {
    if (empty($fechaHoraBD) || $fechaHoraBD === '0000-00-00 00:00:00') {
        return '';
    }
    
    configurarZonaHorariaVenezuela();
    $timestamp = strtotime($fechaHoraBD);
    if ($timestamp === false) {
        return $fechaHoraBD; // Devuelve la fecha original si no se puede parsear
    }
    
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Obtiene el año actual de Venezuela
 * Útil para copyright en el footer
 * 
 * @return string Año actual
 */
function anioActualVenezuela() {
    configurarZonaHorariaVenezuela();
    return date('Y');
}

// Configurar zona horaria automáticamente al incluir este archivo
configurarZonaHorariaVenezuela();
?>