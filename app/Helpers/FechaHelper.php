<?php

namespace App\Pirotecnicafenix\Helpers;


class FechaHelper {
    
    
    private static function configurarZonaHoraria() {
        if (date_default_timezone_get() !== 'America/Caracas') {
            date_default_timezone_set('America/Caracas');
        }
    }
    
   
    public static function ahora($formato = 'Y-m-d H:i:s') {
        self::configurarZonaHoraria();
        return date($formato);
    }
 
    public static function paraDateTimeLocal() {
        self::configurarZonaHoraria();
        return date('Y-m-d\TH:i');
    }
    
   
    public static function paraDate() {
        self::configurarZonaHoraria();
        return date('Y-m-d');
    }
    
   
    public static function paraTime() {
        self::configurarZonaHoraria();
        return date('H:i');
    }
   
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
    
    
    public static function haceAnios($anios, $formato = 'Y-m-d') {
        self::configurarZonaHoraria();
        return date($formato, strtotime("-$anios years"));
    }
    
   
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
    
  
    public static function anioActual() {
        self::configurarZonaHoraria();
        return date('Y');
    }
    
   
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