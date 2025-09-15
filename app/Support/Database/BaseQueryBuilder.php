<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class BaseQueryBuilder extends EloquentQueryBuilder
{
    public function __construct(
        ConnectionInterface $connection,
        ?Grammar $grammar = null,
        ?Processor $processor = null,
    ) {
        parent::__construct($connection, $grammar, $processor);

        // @phpstan-ignore-next-line
        $this->wheres = new WheresCollection();
    }
}
