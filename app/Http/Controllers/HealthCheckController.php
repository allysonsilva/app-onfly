<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Spatie\Health\Health;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\ResultStores\ResultStore;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class HealthCheckController
{
    public function __invoke(Request $request, ResultStore $resultStore, Health $health): JsonResponse|Response|View
    {
        if ($request->has('fresh')) {
            Artisan::call(RunHealthChecksCommand::class);
        }

        $checkResults = $resultStore->latestResults();
        $allChecksOk = $resultStore->latestResults()?->allChecksOk();

        if ($request->has('exception')) {
            if (! $allChecksOk) {
                throw new ServiceUnavailableHttpException(message: 'Application not healthy');
            }
        }

        if ($request->has('view')) {
            return view('health::list', [
                'lastRanAt' => new Carbon($checkResults?->finishedAt),
                'checkResults' => $checkResults,
                'assets' => $health->assets(),
                'theme' => config('health.theme'),
            ]);
        }

        $statusCode = $checkResults?->containsFailingCheck()
            ? config('health.json_results_failure_status', Response::HTTP_INTERNAL_SERVER_ERROR)
            : Response::HTTP_OK;

        return response($checkResults?->toJson() ?? '', $statusCode)
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
}
