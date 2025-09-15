<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Arr;
use Illuminate\Console\Application;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event as EventFacade;

trait CustomMacros
{
    /**
     * Cria macros personalizadas de response.
     *
     * @return void
     */
    protected function testResponseMacros(): void
    {
        TestResponse::macro('assertResource', function (JsonResource $resource): void {
            $responseData = $resource->response()->getData(true);

            $this->assertJson($responseData);
        });
    }

    protected function collectionMacros(): void
    {
        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });
    }

    protected function scheduleMacros(): void
    {
        // Exemplo: $this->assertTrue(Schedule::hasCommand('prune-stale-files', '0 0 * * *'));
        Schedule::macro('hasCommand', function (string $command, string $expression, array $parameters = []): bool {
            $command = Application::formatCommandString(class_exists($command) ? app($command)->getName() : $command);

            if (! empty($parameters)) {
                $command .= ' '.$this->compileParameters($parameters);
            }

            $event = Arr::first(
                $this->events(),
                fn (Event $event) => $event->command === $command
                // fn (Event $item): bool => Str::after($item->command, "'artisan' ") === $command && $item->expression === $expression
            );

            return ! is_null($event);
        });

        // Exemplo: $this->assertTrue(Schedule::hasJob(VerifyUserSubscriptions::class, '0 0 * * *'));
        Schedule::macro('hasJob', function (string $job, string $expression): bool {
            $event = Arr::first(
                $this->events(),
                fn (Event $item): bool => $item->description === $job && $item->expression === $expression
            );

            return ! is_null($event);
        });
    }

    protected function eventMacros(): void
    {
        EventFacade::macro('fakeExceptModels', function (string|array $models, string|array $eventsToAllow = []) {
            $initialEvent = EventFacade::getFacadeRoot();

            if (! empty($eventsToAllow)) {
                EventFacade::fakeExcept($eventsToAllow);
            } else {
                EventFacade::fake();
            }

            // Loop over the desired models and reset their
            // event dispatcher to get those events firing again
            foreach (Arr::wrap($models) as $model) {
                $model::setEventDispatcher($initialEvent);
            }
        });
    }
}
