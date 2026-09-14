<?php
namespace App\Pirotecnicafenix\Middleware;

<<<<<<< HEAD
class AuthMiddleware
{
    //averigua que el usuario este logueado 
=======
use App\Pirotecnicafenix\Helpers\PermisoHelper;

class AuthMiddleware
{
    // Averigua que el usuario esté logueado
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']);
    }

    public static function getUserRole(): ?int
    {
        return isset($_SESSION['id_rol']) ? (int) $_SESSION['id_rol'] : null;
    }

    public static function isAdmin(): bool
    {
        return self::getUserRole() === 1;
    }

    public static function hasRole($rol): bool
    {
        return self::getUserRole() === (int) $rol;
    }

    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ?url=login');
            exit();
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ?url=login');
            exit();
        }

<<<<<<< HEAD
// verifica que el usuario sea administrador 
=======
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
        if (!self::isAdmin()) {
            $_SESSION['error'] = 'Acceso denegado. Se requieren permisos de administrador.';
            header('Location: ?url=dashboard');
            exit();
        }
    }

<<<<<<< HEAD
    public static function checkAccess(string $url): bool
    {
        $publicRoutes = ['main', 'login', ''];
        $authRoutes = ['dashboard', 'productos', 'proveedores', 'clientes', 'categorias', 'notaentrada', 'notasalida', 'reportes'];
=======
    /**
     * ⭐ NUEVO: Verifica si el usuario tiene permiso para un módulo y acción
     * 
     * @param \PDO $db Conexión a la BD
     * @param string $modulo Nombre del módulo (ej: 'Productos', 'Clientes')
     * @param string $accion 'crear', 'leer', 'actualizar', 'eliminar'
     */
    public static function requirePermiso($db, string $modulo, string $accion = 'leer'): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ?url=login');
            exit();
        }

        $id_rol = self::getUserRole() ?? 0;

        if (!PermisoHelper::tienePermiso($db, $id_rol, $modulo, $accion)) {
            $_SESSION['error'] = "No tienes permiso para acceder al módulo de {$modulo}.";
            header('Location: ?url=dashboard');
            exit();
        }
    }

    public static function checkAccess(string $url): bool
    {
        $publicRoutes = ['main', 'login', ''];
        $authRoutes = ['dashboard', 'productos', 'proveedores', 'clientes', 'categorias', 'notaentrada', 'notasalida', 'reportes', 'permisos'];
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
        $adminRoutes = ['usuarios', 'roles'];

        if (in_array($url, $publicRoutes, true)) {
            return true;
        }

        if (in_array($url, $authRoutes, true)) {
            return self::isLoggedIn();
        }

        if (in_array($url, $adminRoutes, true)) {
            return self::isLoggedIn() && self::isAdmin();
        }

        return true;
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
