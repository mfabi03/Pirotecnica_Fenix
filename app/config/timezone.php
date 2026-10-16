<?php

function configurarZonaHorariaVenezuela() {
    
    if (date_default_timezone_get() !== 'America/Caracas') {
    
        date_default_timezone_set('America/Caracas');
        
        
        if (function_exists('ini_set')) {
            ini_set('date.timezone', 'America/Caracas');
        }
        
       
        if (defined('DEBUG') && DEBUG === true) {
            error_log('Zona horaria configurada a: America/Caracas');
        }
    }
}


function fechaHoraVenezuela($formato = 'Y-m-d H:i:s') {
    configurarZonaHorariaVenezuela();
    return date($formato);
}


function fechaParaDateTimeLocal() {
    configurarZonaHorariaVenezuela();
    return date('Y-m-d\TH:i');
}


function fechaParaDate() {
    configurarZonaHorariaVenezuela();
    return date('Y-m-d');
}

function horaParaTime() {
    configurarZonaHorariaVenezuela();
    return date('H:i');
}


function formatoFechaVenezuela($fechaBD) {
    if (empty($fechaBD) || $fechaBD === '0000-00-00') {
        return '';
    }
    
    configurarZonaHorariaVenezuela();
    $timestamp = strtotime($fechaBD);
    if ($timestamp === false) {
        return $fechaBD; 
    }
    
    return date('d/m/Y', $timestamp);
}


function formatoFechaHoraVenezuela($fechaHoraBD) {
    if (empty($fechaHoraBD) || $fechaHoraBD === '0000-00-00 00:00:00') {
        return '';
    }
    
    configurarZonaHorariaVenezuela();
    $timestamp = strtotime($fechaHoraBD);
    if ($timestamp === false) {
        return $fechaHoraBD; 
    }
    
    return date('d/m/Y H:i', $timestamp);
}


function anioActualVenezuela() {
    configurarZonaHorariaVenezuela();
    return date('Y');
}

configurarZonaHorariaVenezuela();
?>