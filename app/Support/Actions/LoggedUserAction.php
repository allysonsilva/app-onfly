<?php

declare(strict_types=1);

namespace App\Support\Actions;

use App\Support\Contracts\ActionInterface;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class LoggedUserAction implements ActionInterface
{
    /**
     * Create a new Action instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $loggedUser
     */
    public function __construct(protected AuthenticatableContract $loggedUser)
    {
    }
}
