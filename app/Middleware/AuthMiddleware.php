<?php
namespace App\Pirotecnicafenix\Middleware;

class AuthMiddleware
{
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

        if (!self::isAdmin()) {
            $_SESSION['error'] = 'Acceso denegado. Se requieren permisos de administrador.';
            header('Location: ?url=dashboard');
            exit();
        }
    }

    public static function checkAccess(string $url): bool
    {
        $publicRoutes = ['main', 'login', ''];
        $authRoutes = ['dashboard', 'productos', 'proveedores', 'clientes', 'categorias', 'notaentrada', 'notasalida', 'reportes'];
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
}
