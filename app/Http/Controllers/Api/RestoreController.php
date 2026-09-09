<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RestoreController extends Controller
{
    /**
     * Unified restore endpoint for soft-deleted models.
     *
     * Supported types: organization, project, task
     */
    public function restore(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:organization,project,task'],
            'id' => ['required', 'string', 'uuid'],
        ]);

        $type = $request->input('type');
        $id = $request->input('id');

        // Map type to model class
        $modelClass = match ($type) {
            'organization' => Organization::class,
            'project' => Project::class,
            'task' => Task::class,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };

        // Find soft-deleted model
        $model = $modelClass::onlyTrashed()->find($id);

        if (! $model) {
            throw ValidationException::withMessages([
                'id' => [__('validation.exists', ['attribute' => $type])],
            ]);
        }

        // Restore the model
        $model->restore();

        $modelName = Str::headline($type);

        return ApiResponse::success(
            data: null,
            message: __('messages.restore_success', ['model' => $modelName]),
        );
    }
}
