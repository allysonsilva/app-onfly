<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HandlesLoggedUser
{
    use HasBinaryUuidV7PrimaryKey;

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (preg_match('/^(?:[A-Z]+-)?(?=[A-Z0-9]{10,}$)(?=.*[A-Z])(?=.*\d)[A-Z0-9]+$/', $value)) {
            return $this->where($field ? $field : 'code', $value)->first();
        }

        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }

    /**
     * @see https://laravel.com/docs/12.x/eloquent#anonymous-global-scopes
     */
    public static function addGlobalScopeWhereLoggedUser(): void
    {
        // Esse escopo global filtra todas as consultas para incluir apenas os registros
        // associados ao usuário autenticado. E será usado também na $builder->firstOrFail()
        // do método acima de `resolveRouteBinding`
        static::addGlobalScope('where-logged-user', function (Builder $builder) {
            if (auth()->check() && auth()->user() instanceof User) {
                // Se o usuário estiver autenticado e não for admin, filtra pelos registros do usuário
                $builder->where("{$builder->getModel()->getTable()}.user_id", auth()->id());
            }
        });
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootHandlesLoggedUser()
    {
        self::addGlobalScopeWhereLoggedUser();

        static::creating(function (self $entity) {
            $entity->user_id = auth()->id() ?: $entity->user_id;
        });
    }
}
