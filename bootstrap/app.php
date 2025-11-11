<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->alias([
            'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
            'onboarding.check' => \App\Http\Middleware\OnboardingMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthorizationException $e, Request $request) {

            // --- Intentar capturar el nombre del permiso fallido ---
            $ability = null;

            // Si se usó authorize('permission'), Laravel guarda el atributo en "ability"
            if (property_exists($e, 'ability')) {
                $ability = $e->ability;
            }

            // Si no existe, tratamos de adivinarlo a partir de la ruta o del mensaje
            if (!$ability) {
                $ability = $e->getMessage();
            }
            $expected = $request->route()?->getName() ?? null;

            $msg = 'This action is unauthorized';
            if ($ability && $ability !== 'This action is unauthorized.') {
                $msg .= " (expected permission: {$ability})";
            } elseif ($expected) {
                $msg .= " (expected permission: {$expected})";
            }

            // API o Web
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], Response::HTTP_FORBIDDEN);
            }

            return response()->view('errors.403', ['message' => $msg], Response::HTTP_FORBIDDEN);
        });

    })
    ->withProviders([
        App\Providers\RateLimiterServiceProvider::class,
    ])
    ->create();
