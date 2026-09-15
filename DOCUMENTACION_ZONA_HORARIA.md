# Configuración de Zona Horaria - Venezuela

## Problema Resuelto
El campo `datetime-local` del formulario de "Registro de Nota de Entrada" (y otros formularios con fechas) mostraba la hora incorrecta - adelantada aproximadamente 4 horas respecto a la hora real de Venezuela.

**Causa**: La zona horaria de PHP estaba configurada como UTC (por defecto en XAMPP). Venezuela usa `America/Caracas` (UTC-4).

## Solución Implementada
Se configuró la zona horaria de Venezuela en 3 niveles:

### 1. Archivo de Configuración (`app/Config/timezone.php`)
```php
// Configura la zona horaria de Venezuela (America/Caracas)
function configurarZonaHorariaVenezuela() {
    if (date_default_timezone_get() !== 'America/Caracas') {
        date_default_timezone_set('America/Caracas');
        if (function_exists('ini_set')) {
            ini_set('date.timezone', 'America/Caracas');
        }
    }
}
```

### 2. Inclusión en el Front Controller (`index.php`)
```php
require __DIR__ . '/app/Config/timezone.php';
```

### 3. Funciones Helper Creadas

#### Funciones Globales (en `timezone.php`):
- `fechaHoraVenezuela($formato)` - Fecha y hora actual en formato especificado
- `fechaParaDateTimeLocal()` - Para campos `datetime-local` (formato: `Y-m-d\TH:i`)
- `fechaParaDate()` - Para campos `date` (formato: `Y-m-d`)
- `horaParaTime()` - Para campos `time` (formato: `H:i`)
- `formatoFechaVenezuela($fechaBD)` - Convierte `YYYY-MM-DD` a `DD/MM/YYYY`
- `formatoFechaHoraVenezuela($fechaHoraBD)` - Convierte `YYYY-MM-DD HH:MM:SS` a `DD/MM/YYYY HH:MM`
- `anioActualVenezuela()` - Año actual para copyright

#### Helper de Clase (`app/Helpers/FechaHelper.php`):
Clase `FechaHelper` con métodos estáticos para uso orientado a objetos.

## Archivos Modificados

### 1. Archivos de Configuración:
- `app/Config/timezone.php` - **(NUEVO)** Configuración principal de zona horaria
- `index.php` - Incluye la configuración de zona horaria
- `app/Helpers/FechaHelper.php` - **(NUEVO)** Helper para fechas venezolanas

### 2. Vistas Corregidas:
- `app/view/nota_entrada/registroNotaEntradaView.php` - Campo `datetime-local` corregido
- `app/view/footer.php` - Año del copyright corregido
- `app/view/clientes/detalleClientView.php` - Formato de fecha corregido
- `app/view/nota_entrada/listNotaEntradaView.php` - Formato de fecha corregido
- `app/view/nota_entrada/detalleNotaEntradaView.php` - Formato de fecha corregido
- `app/view/nota_salida/listNotasalidaView.php` - Formato de fecha corregido
- `app/view/nota_salida/detalleNotasalidaView.php` - Formato de fecha corregido (3 ocurrencias)
- `app/view/proveedores/verProveedorView.php` - Formato de fecha corregido

## Cómo Usar las Nuevas Funciones

### En Vistas (HTML/PHP):
```php
<!-- Para campos datetime-local -->
<input type="datetime-local" value="<?= fechaParaDateTimeLocal() ?>">

<!-- Para mostrar fechas de BD -->
<p>Fecha: <?= formatoFechaVenezuela($fechaDesdeBD) ?></p>
<p>Fecha y Hora: <?= formatoFechaHoraVenezuela($fechaHoraDesdeBD) ?></p>

<!-- Para copyright -->
<span>&copy; <?= anioActualVenezuela() ?></span>
```

### En Controladores (PHP):
```php
// Usando funciones globales
$fechaActual = fechaHoraVenezuela('Y-m-d H:i:s');

// Usando la clase helper
use App\Pirotecnicafenix\Helpers\FechaHelper;
$fechaFormateada = FechaHelper::formatoVenezolanoConHora($fechaBD);
```

## Verificación del Cambio

Para verificar que la configuración funciona:

1. **Zona horaria configurada**: `America/Caracas` (UTC-4)
2. **Diferencia con UTC**: 4 horas
3. **Campo datetime-local**: Ahora muestra la hora correcta de Venezuela
4. **Formato de fechas**: Las fechas se muestran en formato venezolano (DD/MM/YYYY)

## Notas Técnicas

- Venezuela no tiene horario de verano
- La zona horaria `America/Caracas` maneja automáticamente UTC-4
- Las funciones `strtotime()` ahora interpretan las fechas en zona horaria de Venezuela
- Los campos de formulario `datetime-local` requieren formato `YYYY-MM-DDTHH:MM`

## Impacto en el Sistema

- ✅ Los formularios muestran la hora correcta de Venezuela
- ✅ Las fechas en listados y detalles se muestran en formato venezolano
- ✅ El año del copyright se actualiza automáticamente
- ✅ Compatibilidad con fechas de base de datos
- ✅ No afecta la lógica de negocio existente

## Archivos que Podrían Necesitar Corrección en el Futuro

Al buscar `date(` en el código, se encontraron estos archivos adicionales:
- `app/Controller/clientesController.php` - Usa `date()` para fecha por defecto (18 años atrás)
- `app/Controller/ReportesController.php` - Usa `date()` para nombre de archivo CSV
- `app/Model/notasalidaModel.php` - Usa `CURDATE()` de MySQL
- `app/view/clientes/editarClientView.php` - Campo `date` (no necesita cambio)
- `app/view/clientes/registroClientView.php` - Campo `date` (no necesita cambio)
- `app/view/reportes/reportesView.php` - Campo `date` (no necesita cambio)

Estos archivos funcionan correctamente y no requieren cambios inmediatos.