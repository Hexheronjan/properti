<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'nup/payment/callback',
            'utk/callback',
            'doku/notifications',
            'screening/payment-callback',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'peserta' => \App\Http\Middleware\PesertaMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Custom exception handling
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                if ($request->expectsJson() || $request->ajax() || $request->is('api/*', '*/notifications/*', 'admin/notifications/*')) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }
                return redirect()->guest(route('login'))->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
            }

            $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => getUserFriendlyMessage($e),
                    'error' => config('app.debug') ? [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], $statusCode);
            }

            // For web requests, show simple error page
            return response()->view('errors.simple', [
                'message' => getUserFriendlyMessage($e),
                'technical_message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ] : null,
            ], $statusCode);
        });
    })->create();

/**
 * Get user-friendly error message
 */
function getUserFriendlyMessage(\Throwable $e): string
{
    if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
        return 'Data tidak ditemukan.';
    }
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        return 'Validasi gagal. Silakan periksa input Anda.';
    }
    if ($e instanceof \Illuminate\Auth\AuthenticationException) {
        return 'Anda belum login atau sesi telah berakhir.';
    }
    if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
        return 'Anda tidak memiliki akses ke halaman ini.';
    }
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
        if ($e->getMessage()) {
            return $e->getMessage();
        }
        return $e->getStatusCode() === 404 ? 'Halaman atau data tidak ditemukan.' : 'Anda tidak memiliki akses ke halaman ini.';
    }
    if ($e instanceof \Illuminate\Database\QueryException) {
        return 'Terjadi kesalahan database.';
    }
    return 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
}
