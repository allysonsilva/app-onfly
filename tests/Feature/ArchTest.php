<?php

declare(strict_types=1);

describe('arch tests', function () {
    arch('denied methods')
        ->expect([
            'dump',
            'dd',
            'echo',
            'var_dump',
            'env',
        ])
        ->not->toBeUsed();

    arch('models')
        ->expect('\App\Models')
        ->toOnlyUse([
            'Enum',
            'Illuminate\Database',
            'Illuminate\Support',
        ]);
})->group('arch');
