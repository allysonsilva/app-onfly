<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class MySqlConnection extends BaseMySqlConnection
{
    public function query(): BaseQueryBuilder
    {
        return new BaseQueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}
