<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnboardingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Si no hay usuario autenticado, dejar pasar (el middleware auth se encargará)
        if (!$user) {
            return $next($request);
        }

        // Si el usuario es admin, no forzar onboarding
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Verificar si tiene onboarding
        $onboarding = $user->onboarding;

        // Si no tiene onboarding o ya está completado, continuar
        if (!$onboarding || $onboarding->isCompleted()) {
            return $next($request);
        }

        // Si está en una ruta de onboarding, permitir acceso
        if ($request->is('admin/onboarding/*')) {
            return $next($request);
        }

        // Si el onboarding no está completado, redirigir al paso actual
        $currentStep = $onboarding->current_step;

        return redirect()->route("onboarding.step{$currentStep}");
    }
}
