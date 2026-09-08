<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->list(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 15),
            projectId: $request->input('project_id'),
            status: $request->input('status'),
            priority: $request->input('priority'),
            assignedTo: $request->input('assigned_to'),
            assignedToMe: $request->boolean('assigned_to_me'),
            search: $request->input('search'),
        );

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->create(
                data: $request->validated(),
                creator: $request->user(),
            );

            return (new TaskResource($task->load(['project', 'user', 'creator'])))
                ->additional([
                    'message' => __('task.created_successfully'),
                ])
                ->response()
                ->setStatusCode(201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        $task = $this->taskService->getById($task->id);

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $updated = $this->taskService->update(
                task: $task,
                data: $request->validated(),
                updater: $request->user(),
            );

            return (new TaskResource($updated->load(['project', 'user', 'creator'])))
                ->additional([
                    'message' => __('task.updated_successfully'),
                ])
                ->response();
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task, Request $request): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete(
            task: $task,
            deletedBy: $request->user(),
        );

        return response()->json([
            'message' => __('task.deleted_successfully'),
        ]);
    }

    /**
     * Restore the specified task.
     */
    public function restore(string $id, Request $request): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);

        $this->authorize('restore', $task);

        $this->taskService->restore($task);

        return (new TaskResource($task->fresh()->load(['project', 'user', 'creator'])))
            ->additional([
                'message' => __('task.restored_successfully'),
            ])
            ->response();
    }

    /**
     * Assign task to user.
     */
    public function assign(AssignTaskRequest $request, Task $task): JsonResponse
    {
        $assignee = User::findOrFail($request->input('user_id'));

        try {
            $updated = $this->taskService->assign(
                task: $task,
                assignee: $assignee,
            );

            return (new TaskResource($updated->load(['project', 'user', 'creator'])))
                ->additional([
                    'message' => __('task.assigned_successfully'),
                ])
                ->response();
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unassign task from current user.
     */
    public function unassign(Request $request, Task $task): JsonResponse
    {
        $this->authorize('assign', $task);

        $updated = $this->taskService->unassign($task);

        return (new TaskResource($updated->load(['project', 'user', 'creator'])))
            ->additional([
                'message' => __('task.unassigned_successfully'),
            ])
            ->response();
    }

    /**
     * Get overdue tasks.
     */
    public function overdue(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->getOverdueTasks(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 15),
        );

        return TaskResource::collection($tasks);
    }
}
