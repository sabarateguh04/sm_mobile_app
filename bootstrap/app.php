<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            require __DIR__.'/../routes/satupeta.php';
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // HTTPS terminators (nginx, Cloudflare, load balancers) send X-Forwarded-*; without this,
        // Laravel may treat requests as HTTP and session/CSRF cookies can fail → 419 on POST /login.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'level' => \App\Http\Middleware\CheckLevel::class,
            'usm.active' => \App\Http\Middleware\EnsureUsmAccountIsActive::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'role.mutation' => \App\Http\Middleware\EnforceRoleMutationPolicy::class,
            'satupeta.session' => \App\Http\Middleware\EnsureSatupetaSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
