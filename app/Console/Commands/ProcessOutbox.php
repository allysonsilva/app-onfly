<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OutboxMessage;
use App\Support\Outbox\OutboxJob;
use App\Support\Outbox\OutboxMessageStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class ProcessOutbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-outbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process outbox messages and dispatch them to their respective handlers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        OutboxMessage::where('status', OutboxMessageStatus::PENDING)
            ->orderBy('id')
            ->chunkById(50, function (Collection $messages) {
                foreach ($messages as $message) {
                    try {
                        $message->update([
                            'status' => OutboxMessageStatus::PROCESSING,
                        ]);

                        OutboxJob::create(unserialize($message->payload['command']), $message);
                    } catch (Throwable $th) {
                        $message->update([
                            'status' => OutboxMessageStatus::FAILED,
                            'attempts' => ++$message->attempts,
                        ]);

                        $this->error("Outbox #{$message->getKey()} failed: " . $th->getMessage());
                    }
                }
            });
    }
}
