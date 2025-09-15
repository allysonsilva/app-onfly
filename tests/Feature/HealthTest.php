<?php

declare(strict_types=1);

it("it's up", function () {
    \Pest\Laravel\get('/up')->assertOk();
});
