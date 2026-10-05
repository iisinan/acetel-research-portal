<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'role:Admin|Director'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
            
            Route::middleware(['web', 'auth', 'role:Program Coordinator|Admin|Director'])
                ->prefix('coordinator')
                ->name('coordinator.')
                ->group(base_path('routes/coordinator.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->back()
                    ->withInput($request->except(['password', '_token']))
                    ->with('error', 'Your session expired due to inactivity. Please try again.')
                    ->withErrors(['session' => 'Your session expired due to inactivity. Please try again.']);
            }
            
            if ($request->is('milestones*')) {
                return response($e->getMessage() . "\nIn " . $e->getFile() . " on line " . $e->getLine() . "\n\n" . $e->getTraceAsString(), 500)
                    ->header('Content-Type', 'text/plain');
            }
        });
    })->create();
