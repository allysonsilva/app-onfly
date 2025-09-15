<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Sentry\configureScope as sentryConfigureScope;

/**
 * @codeCoverageIgnore
 */
final class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        sentryConfigureScope(function (Scope $scope): void {
            $scope->setTag('application.name', config('app.name'));
        });

        if (auth()->check() && app()->bound('sentry')) {
            sentryConfigureScope(function (Scope $scope): void {
                $scope->setUser([
                    'id' => auth()->id(),
                    // 'email' => auth()->user()->email,
                ]);

                $scope->setTag('environment', config('app.env'));
            });
        }

        return $next($request);
    }
}
