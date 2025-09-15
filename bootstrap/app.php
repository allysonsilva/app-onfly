<?php

declare(strict_types=1);

use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\SentryContext;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: base_path('routes/api.php'),
        health: '/up',
        apiPrefix: '',
        then: function () {
            Route::middleware('api')
                ->name('v1.auth.')
                ->prefix('v1/auth')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withCommands([
        app_path('Console/Commands'),
        base_path('routes/console.php'),
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware
            ->throttleApi(limiter: 'api', redis: true)
            ->redirectTo(guests: fn () => null)
            ->alias([
                'abilities' => CheckAbilities::class,
                'ability' => CheckForAnyAbility::class,
            ])
            ->append(IdempotencyMiddleware::class)
            ->append(SentryContext::class)
            ->replace(Illuminate\Http\Middleware\TrustProxies::class, TrustProxies::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions
            ->shouldRenderJsonWhen(static fn () => true)
            ->dontReportDuplicates()
            ->dontReport([
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            ])
            ->stopIgnoring([
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                \Illuminate\Http\Exceptions\HttpResponseException::class,
                \Illuminate\Database\Eloquent\ModelNotFoundException::class,
                \Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface::class,
            ])->reportable(static function (Throwable $exception) {
                // if (! app()->runningUnitTests()) {
                //     Sentry\Laravel\Integration::captureUnhandledException($exception);

                //     return false;
                // }
            });
    })
    ->create();
