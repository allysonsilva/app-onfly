<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Str;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Telescope;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Telescope::night();

        Telescope::tag(function (IncomingEntry $entry) {
            $tags = [];

            if ($entry->type === EntryType::REQUEST) {
                $tags = array_merge([
                    'status:' . $entry->content['response_status'],
                    'uri:' . Str::slug(trim(parse_url($entry->content['uri'], PHP_URL_PATH), '/')),
                ], $tags);
            }

            return $tags;
        });

        Telescope::filter(fn (IncomingEntry $entry) => true);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', fn ($user) => true);
    }
}
