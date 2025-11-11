<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Evita ejecutar todo el bloque durante la instalación o en consola
        if (
            App::runningInConsole() ||
            (!empty($_SERVER['REQUEST_URI']) &&
                (strpos($_SERVER['REQUEST_URI'], '/install') !== false ||
                    strpos($_SERVER['REQUEST_URI'], '/forceinstall') !== false))
        ) {
            return;
        }

        // Forzar HTTPS en producción
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }

        // Cargar permisos por rol desde el config
        $permissionsRoles = config('constants.permissions_by_role');

        $all_permissions = [];
        foreach ($permissionsRoles as $role => $permissions) {
            $all_permissions = array_merge($all_permissions, $permissions);
        }
        $all_permissions = array_unique($all_permissions);

        // Definir Gates dinámicamente
        foreach ($all_permissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->checkAccess($permission);
            });
        }
    }

}
