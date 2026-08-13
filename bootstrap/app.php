<?php

use App\Exceptions\Invoice\InvoiceAlreadyPaidException;
use App\Exceptions\Invoice\InvoiceCannotBeDeletedException;
use App\Exceptions\Invoice\InvoiceCannotBeReissuedException;
use App\Exceptions\Invoice\InvoiceCannotBeRestoredException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (InvoiceAlreadyPaidException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (InvoiceCannotBeDeletedException $e) {
            return response()->json([
                'message' => 'Invoice cannot be deleted',
            ], 422);
        });

        $exceptions->render(function (InvoiceCannotBeRestoredException $e) {
            return response()->json([
                'message' => 'Invoice cannot be restored',
            ], 422);
        });

        $exceptions->render(function (InvoiceCannotBeReissuedException $e) {
            return response()->json([
                'message' => 'Invoice cannot be reissued',
            ], 422);
        });
    })->create();
