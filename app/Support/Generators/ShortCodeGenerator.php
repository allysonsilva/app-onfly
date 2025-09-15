<?php

declare(strict_types=1);

namespace App\Support\Generators;

use GMP;

final class ShortCodeGenerator
{
    private const CROCKFORD_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    private const TIMESTAMP_BITS = 48; // 48 bits do UUIDv7 (timestamp)

    // Até ~16,7 milhões de códigos únicos podem ser gerados por milissegundo.
    private const EXTRA_BITS = 24; // 24 bits extras (aleatórios para garantir unicidade)

    private const TOTAL_BITS = self::TIMESTAMP_BITS + self::EXTRA_BITS;

    public function __construct(
        private string $prefix = ''
    ) {
    }

    public function withPrefix(string $prefix): self
    {
        $this->prefix = strtoupper($prefix);

        return $this;
    }

    public function generate(?string $identifier = null): string
    {
        $identifier = preg_replace('/[^A-Za-z0-9]/', '', $identifier);

        // Cada hex = 4 bits → precisamos de TOTAL_BITS bits
        $hexLength = (int) ceil(self::TOTAL_BITS / 4);

        // Pega os primeiros hexLength caracteres
        // Ex.: 01992eaa9fc77f01 - com até 18 caracteres → representa 72 bits
        $shortHex = substr($identifier, 0, $hexLength);

        // PHP nativamente só garante precisão segura para inteiros de 64 bits (PHP_INT_MAX),
        // ou seja, números muito grandes não cabem em um int normal.
        //
        // Para 72 bits ou mais, precisamos de arithmetic arbitrary-precision
        // e é aí que entra o GMP (GNU Multiple Precision).
        //
        // Garante que cada bit do timestamp + extra seja preservado na conversão para Base32.
        //
        // @example GMP {#2017
        //   value: 29484782328357569205
        // }
        $num = gmp_init($shortHex, 16);
        // Agora $num é um grande número que representa os bits necessários.

        // Converte para Base32 Crockford
        $code = $this->gmpToCrockfordBase32($num);

        return strtoupper(trim($this->prefix.$code));
    }

    /**
     * Converte um número positivo em Base32 usando o alfabeto de Crockford.
     *
     * Utilizado o alfabeto de Crockford (sem I, L, O e U para evitar confusões, letras ambíguas):
     *  - Difícil de ler ou ditar em voz alta - propenso a erro humano.
     *  - Mais legível visualmente
     *  - Mais seguro para digitação manual ou impressão
     *  - Menos propenso a erros de comunicação
     */
    private function gmpToCrockfordBase32(GMP $num): string
    {
        $code = '';

        while (gmp_cmp($num, 0) > 0) {
            // Pega o próximo dígito Base32:
            // 1. gmp_mod($num, 32) retorna o resto da divisão por 32 (0-31)
            // 2. gmp_intval() converte de GMP object para inteiro normal
            $rem = gmp_intval(gmp_mod($num, 32));

            // Concatena o caractere Base32 correspondente ao dígito calculado ($rem)
            // no início da string $code, invertendo a ordem dos dígitos
            $code = self::CROCKFORD_ALPHABET[$rem].$code;

            // Remove o dígito já processado dividindo $num por 32 (desloca 5 bits à direita)
            // Preparando o próximo dígito Base32 na próxima iteração
            $num = gmp_div_q($num, 32);
        }

        return $code;
    }
}
