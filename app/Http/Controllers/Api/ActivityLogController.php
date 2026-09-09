<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\ApiResourceCollection;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * List activity logs.
     */
    public function index(Request $request): ApiResourceCollection
    {
        $logs = $this->activityLogService->list(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 50),
            targetType: $request->input('target_type'),
            targetId: $request->input('target_id'),
            userId: $request->input('user_id'),
        );

        return (new ApiResourceCollection($logs))
            ->setResourceClass(ActivityLogResource::class)
            ->withMessage(__('activity.list_retrieved'));
    }
}
