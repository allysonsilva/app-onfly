<?php

declare(strict_types=1);

namespace App\Providers;

use Spatie\Health\Facades\Health;
use Illuminate\Support\ServiceProvider;

use Spatie\Health\Checks\Checks\{
    RedisCheck,
    CacheCheck,
    QueueCheck,
    ScheduleCheck,
    DatabaseCheck,
    DebugModeCheck,
    EnvironmentCheck,
    OptimizedAppCheck
};

class HealthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $checks = [
            CacheCheck::new(),
            RedisCheck::new(),
            DatabaseCheck::new()->name('Main Database')->connectionName('mysql'),
            OptimizedAppCheck::new(),
            // By default, the QueueCheck will fail when the job dispatched by DispatchQueueCheckJobsCommand isn't handled within 5 minutes.
            QueueCheck::new()->failWhenHealthJobTakesLongerThanMinutes(5),
            // The ScheduleCheckHeartbeatCommand will write the current timestamp into the cache.
            // The ScheduleCheck will verify that that timestamp is not over a minute.
            ScheduleCheck::new()->heartbeatMaxAgeInMinutes(2),
        ];

        if ($this->app->isProduction()) {
            $checks = array_merge($checks, [DebugModeCheck::new(), EnvironmentCheck::new()]);
        }

        Health::checks($checks);
    }
}
