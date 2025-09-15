<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class Register
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        event(new Registered(($user = User::create($validated))));

        return UserResource::make($user)
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
