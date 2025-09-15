<?php

declare(strict_types=1);

namespace App\Support\Outbox;

use App\Models\OutboxMessage;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingClosureDispatch;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\SerializableClosure\SerializableClosure;

class OutboxJob implements ShouldQueue
{
    use Queueable;

    /**
     * Indicate if the job should be deleted when models are missing.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SerializableClosure $closure,
        public OutboxMessage $outboxMessage
    ) {
    }

    /**
     * Create a new job instance.
     */
    public static function create(SerializableClosure $closure, OutboxMessage $outboxMessage): PendingClosureDispatch
    {
        return new PendingClosureDispatch(new self($closure, $outboxMessage));
    }

    /**
     * Execute the job.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     *
     * @return void
     */
    public function handle(Container $container)
    {
        $container->call($this->closure->getClosure(), ['job' => $this]);

        $this->outboxMessage->update([
            'status' => OutboxMessageStatus::DONE,
            'processed_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(): void
    {
        $this->outboxMessage->update([
            'attempts' => ++$this->outboxMessage->attempts,
            'status' => OutboxMessageStatus::FAILED,
        ]);
    }

    /**
     * Get the display name for the queued job.
     */
    public function displayName(): string
    {
        return $this->outboxMessage->payload['displayName'];
    }
}
