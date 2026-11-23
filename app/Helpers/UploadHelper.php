<?php
namespace App\Pirotecnicafenix\Helpers;

use Exception;

class UploadHelper
{
    private const TAMANO_MAXIMO = 5242880;
    private const RUTA_RELATIVA = 'public/uploads/productos/';

    public static function guardarImagenProducto(?array $archivo): ?string
    {
        if ($archivo === null || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception('No se pudo recibir la imagen del producto.');
        }

        $rutaTemporal = $archivo['tmp_name'] ?? '';
        if ($rutaTemporal === '' || !is_uploaded_file($rutaTemporal)) {
            throw new Exception('El archivo de imagen recibido no es válido.');
        }

        $tamano = filesize($rutaTemporal);
        if ($tamano === false || $tamano < 1 || $tamano > self::TAMANO_MAXIMO) {
            throw new Exception('La imagen debe pesar menos de 5 MB.');
        }

        $infoImagen = getimagesize($rutaTemporal);
        $mime = $infoImagen['mime'] ?? '';
        $extensionesPermitidas = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeDetectado = $finfo->file($rutaTemporal);
        if (!$infoImagen || $mime !== $mimeDetectado || !isset($extensionesPermitidas[$mime])) {
            throw new Exception('Formato de imagen no permitido. Usa JPG, PNG o WebP.');
        }

        $directorio = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, self::RUTA_RELATIVA);
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new Exception('No se pudo preparar el directorio de imágenes de productos.');
        }

        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
        if (!move_uploaded_file($rutaTemporal, $directorio . $nombreArchivo)) {
            throw new Exception('No se pudo guardar la imagen del producto.');
        }

        return self::RUTA_RELATIVA . $nombreArchivo;
    }

    public static function eliminarImagenProducto(string $rutaRelativa): void
    {
        if (strpos($rutaRelativa, self::RUTA_RELATIVA) !== 0) {
            throw new Exception('La ruta de imagen del producto no es válida.');
        }

        $nombreArchivo = substr($rutaRelativa, strlen(self::RUTA_RELATIVA));
        if ($nombreArchivo === '' || basename($nombreArchivo) !== $nombreArchivo) {
            throw new Exception('La ruta de imagen del producto no es válida.');
        }

        $rutaArchivo = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, self::RUTA_RELATIVA)
            . $nombreArchivo;
        if (is_file($rutaArchivo) && !unlink($rutaArchivo)) {
            throw new Exception('No se pudo limpiar la imagen del producto.');
        }
    }
}
