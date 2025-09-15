<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CancelTravelRequestAction;
use App\DataObjects\TravelRequestData;
use App\Http\Requests\IndexTravelRequestRequest;
use App\Http\Requests\StoreTravelRequestRequest;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Queries\ListSearchableTravelRequestsQuery;
use App\Support\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TravelRequestController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTravelRequestRequest $request, ListSearchableTravelRequestsQuery $query)
    {
        return $query->paginate($request());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTravelRequestRequest $request): JsonResponse
    {
        $data = TravelRequestData::from($request->validated());

        // Para garantir a integridade em caso de falhas na conexão com o banco de dados
        // podemos tentar a operação de criação algumas vezes com um delay entre elas
        // isso é especialmente útil em ambientes com alta latência ou instabilidade
        // a função retry é uma abstração para facilitar essa lógica
        $newTravelRequest = retry(
            [10, 20],
            fn () => TravelRequest::create($data->toArray())
        );

        return (new TravelRequestResource($newTravelRequest))
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(TravelRequest $travelRequest): TravelRequestResource
    {
        return new TravelRequestResource($travelRequest);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TravelRequest $travelRequest, CancelTravelRequestAction $action): Response
    {
        $action->handle($travelRequest);

        return response()->noContent();
    }
}
