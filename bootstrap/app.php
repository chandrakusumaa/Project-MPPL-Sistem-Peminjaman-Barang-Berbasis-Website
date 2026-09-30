<?php

use App\Http\Middleware\EnsureUserHasOrganizationRole;
use App\Http\Middleware\EnsureUserIsOrganizationMember;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'organization.member' => EnsureUserIsOrganizationMember::class,
            'organization.role' => EnsureUserHasOrganizationRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
