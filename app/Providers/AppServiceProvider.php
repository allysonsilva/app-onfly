<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * @phpcsSuppress
         */
        // Connection::resolverFor(
        //     'mysql',
        //     fn (Closure $connection, string $database, ?string $prefix, array $config) => new MySqlConnection($connection, $database, $prefix, $config)
        // );

        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => $request->user()
                ? Limit::perSecond(10)->by($request->user()->getKey())
                : Limit::perSecond(5)->by($request->ip()));

        Blueprint::macro('uuidV7', fn ($column = 'id') => $this->addColumn('binary', $column, [
            'length' => 16,
        ])->primary());
    }
}
