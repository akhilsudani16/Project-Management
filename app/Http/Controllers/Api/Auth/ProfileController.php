<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        return (new UserResource($request->user()->load('role')))
            ->withMessage(__('auth.profile_retrieved'))
            ->toResponse($request);
    }
}
