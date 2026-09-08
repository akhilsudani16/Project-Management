<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeController extends Controller
{
    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResource
    {
        return new UserResource($request->user()->load('role'));
    }
}
