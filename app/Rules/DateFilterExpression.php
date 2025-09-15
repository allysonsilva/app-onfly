<?php

declare(strict_types=1);

namespace App\Rules;

use App\DataObjects\DateSearchData;
use App\Enums\FilterOperator;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class DateFilterExpression implements ValidationRule
{
    private ?FilterOperator $operator = null;

    private ?Carbon $value = null;

    /**
     * Run the validation rule.
     *
     * Valida se o valor começa com um operador (<, >, <=, >=, =, !=) e contém uma data Y-m-d válida.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // regex captura opcional operador + valor
        if (! preg_match('/^(<=|>=|=|<|>|!=)?(.+)$/', $value, $matches)) {
            $fail("O campo {$attribute} tem um formato inválido.");

            return;
        }

        $this->operator = FilterOperator::tryFrom($matches[1]);

        try {
            // validar a data no formato Y-m-d
            $this->value = Carbon::createFromFormat('Y-m-d', trim($matches[2]))->startOfDay();
        } catch (Exception) {
            $this->value = null;

            $fail("O campo {$attribute} deve ser uma data válida no formato Y-m-d.");
        }
    }

    public function data(): ?DateSearchData
    {
        if (empty($this->value)) {
            return null;
        }

        return new DateSearchData($this->operator, $this->value);
    }
}
