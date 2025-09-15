<?php

declare(strict_types=1);

it('testing migrations executing with success', function () {
    $this->artisan('migrate')->assertSuccessful();

    $this->artisan('migrate:rollback')->assertSuccessful();

    $this->artisan('migrate')->assertSuccessful();
})->group('migration');
