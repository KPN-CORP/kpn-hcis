<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Http\Request;
use App\Exceptions\InvalidOrderException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Di stage/production aplikasi berada di belakang reverse proxy
        // (Cloudflare + web server) yang menterminasi TLS, jadi Laravel
        // melihat request sebagai http. Akibatnya route()/url() menghasilkan
        // URL http:// di halaman yang disajikan lewat https, dan browser
        // memblokirnya sebagai "mixed active content" (mis. ajax DataTables
        // di /medical/confirmation). Mempercayai header X-Forwarded-*
        // membuat scheme, host dan port mengikuti request asli dari client.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function (InvalidOrderException $e, Request $request) {
        return response()->view('errors.403', [], 403);
    });
    })->create();
