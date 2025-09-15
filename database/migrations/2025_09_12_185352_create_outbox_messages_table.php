<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Outbox\OutboxMessageEvent;
use App\Support\Outbox\OutboxMessageStatus;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('event', OutboxMessageEvent::values())->index();
            $table->json('payload');
            $table->enum('status', OutboxMessageStatus::values())
                ->default(OutboxMessageStatus::PENDING->value)
                ->index();
            $table->string('aggregate_type')->nullable();
            $table->string('aggregate_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->datetime('processed_at')->nullable();
            $table->datetimes();

            $table->index(['aggregate_type', 'aggregate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
    }
};
