<?php
namespace App\Pirotecnicafenix\Middleware;

use App\Pirotecnicafenix\Helpers\PermisoHelper;

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

}