<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'is_device' => \App\Http\Middleware\EnsureIsDevice::class,
            'is_user' => \App\Http\Middleware\EnsureIsUser::class,
            'rtdn' => \App\Http\Middleware\VerifyRtdnSecret::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return the spec error envelope {status:false, message} for API/JSON
        // requests on every error path, instead of Laravel's default shapes.
        $wantsEnvelope = fn ($request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($wantsEnvelope) {
            if ($wantsEnvelope($request)) {
                return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($wantsEnvelope) {
            if ($wantsEnvelope($request)) {
                return response()->json([
                    'status' => false,
                    'message' => $e->validator->errors()->first(),
                ], 400);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) use ($wantsEnvelope) {
            if ($wantsEnvelope($request)) {
                return response()->json(['status' => false, 'message' => 'Not found.'], 404);
            }
        });

        $exceptions->render(function (\Throwable $e, $request) use ($wantsEnvelope) {
            // Let HTTP exceptions (e.g. 403, 429) keep their own status code, but
            // still wrap them in the envelope. Generic throwables become a 500.
            if (!$wantsEnvelope($request)) {
                return null;
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $message = $status >= 500 && !config('app.debug')
                ? 'Internal server error.'
                : ($e->getMessage() ?: 'Internal server error.');

            return response()->json(['status' => false, 'message' => $message], $status);
        });
    })->create();
