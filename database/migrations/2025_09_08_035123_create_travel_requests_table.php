<?php

declare(strict_types=1);

use App\Enums\TravelRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->uuidV7('id');
            $table->string('code', 32)->unique();

            $table->foreignId('user_id')->constrained('users');

            $table->json('requester');
            $table->string('destination');
            $table->date('departure_date')->index();
            $table->date('return_date')->index();

            $table->enum('status', TravelRequestStatus::values())
                ->default(TravelRequestStatus::REQUESTED->value)
                ->index();

            $table->datetime('canceled_at')->nullable()->index();
            $table->datetime('approved_at')->nullable()->index();

            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
