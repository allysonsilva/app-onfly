<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @see https://docs.pagar.me/docs/o-que-%C3%A9
 * @see https://docs.stripe.com/api/idempotent_requests
 * @see https://docs.malga.io/documentations/more/idempotency
 */
final class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = strtolower((string) $request->header('Idempotency-Key'));

        if (empty($idempotencyKey) || $request->method() === 'GET') {
            return $next($request);
        }

        $cacheKey = 'idempotency';

        // Tente obter um lock exclusivo para a chave, evitando requisições duplicadas simultâneas
        // Esse lock PODE durar até 60 segundos, mas se o código abaixo terminar antes, então, o lock é liberado
        // para outras requisições com a mesma chave, permitindo que elas sejam processadas
        $lock = Cache::lock("{$cacheKey}:{$idempotencyKey}:lock", 10);

        // Sleep for 100ms on first retry, 200ms on second retry, 1000ms on third retry...
        try {
            retry([100, 200, 1000], function () use ($lock) {
                // Se não conseguir o lock, significa que outra requisição com a mesma chave está em processamento
                // nesse caso, cabe ao client fazer ou ter uma política de retry/backoff
                if (! $lock->get()) {
                    throw new RuntimeException('Could not obtain lock');
                }
            });
        } catch (Exception) {
            $lock->release();

            // Se não conseguir o lock após as tentativas, retorna um erro 409 Conflict
            return response()->json(['error' => 'Duplicate request in progress'], HttpResponse::HTTP_CONFLICT);
        }

        if ($response = $this->getDataFromCache($cacheKey, $idempotencyKey)) {
            $lock->release();

            return $response;
        }

        // Processa a requisição, pois é a primeira vez que a chave é vista
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $this->saveResponseInCache($response, $cacheKey, $idempotencyKey);

        $lock->release(); // Libera o lock

        return $response;
    }

    private function getDataFromCache(string $cacheKey, string $idempotencyKey): Response|false
    {
        $data = Redis::hGet($cacheKey, $idempotencyKey);

        // Checa se a resposta já existe no cache
        if (! empty($data)) {
            $response = unserialize($data);

            return response($response['content'], $response['statusCode'], $response['headers']);
        }

        return false;
    }

    private function saveResponseInCache(Response $response, string $cacheKey, string $idempotencyKey): void
    {
        // Armazena a resposta no cache por um tempo limitado (ex: 24 horas)
        Redis::hSet($cacheKey, $idempotencyKey, serialize([
            'content' => $response->getContent(),
            'statusCode' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
        ]));

        // Define um tempo de expiração para o campo '$idempotencyKey' usando rawCommand
        // O comando HEXPIRE aceita a chave, o tempo de expiração em segundos, o número de campos e os nomes dos campos.
        // A sintaxe é 'HEXPIRE', 'chave', 'segundos', 'FIELDS', 'num_campos', 'campo_1', ...
        // Disponível a partir do Redis 7.4 a possibilidade de expirar campos individuais em hashes
        $resultHexpire = Redis::executeRaw([ // @phpstan-ignore-line
            'HEXPIRE',
            $cacheKey,
            60 * 60 * 24, // 1 dia em segundos
            'FIELDS',
            1,
            $idempotencyKey,
        ]);

        if (! $resultHexpire) {
            // Se falhar, logamos para fins de depuração
            Log::error("Falha ao definir a expiração para o campo {$idempotencyKey} no cache de idempotência.");
        }
    }
}
