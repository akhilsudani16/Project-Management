<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\AssignUserRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    /**
     * Display a listing of projects.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->list(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 15),
            organizationId: $request->input('organization_id'),
            status: $request->input('status'),
            search: $request->input('search'),
            assignedToMe: $request->boolean('assigned_to_me'),
        );

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(
            data: $request->validated(),
            creator: $request->user(),
        );

        return (new ProjectResource($project->load(['organization', 'createdBy'])))
            ->additional([
                'message' => __('project.created_successfully'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        $project = $this->projectService->getById($project->id);

        return new ProjectResource($project);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        try {
            $updated = $this->projectService->update(
                project: $project,
                data: $request->validated(),
                updater: $request->user(),
            );

            return (new ProjectResource($updated->load(['organization', 'createdBy'])))
                ->additional([
                    'message' => __('project.updated_successfully'),
                ])
                ->response();
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project, Request $request): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->delete(
            project: $project,
            deletedBy: $request->user(),
        );

        return response()->json([
            'message' => __('project.deleted_successfully'),
        ]);
    }

    /**
     * Restore the specified project.
     */
    public function restore(string $id, Request $request): JsonResponse
    {
        $project = Project::withTrashed()->findOrFail($id);

        $this->authorize('restore', $project);

        $this->projectService->restore($project);

        return (new ProjectResource($project->fresh()->load(['organization', 'createdBy'])))
            ->additional([
                'message' => __('project.restored_successfully'),
            ])
            ->response();
    }

    /**
     * Display project members.
     */
    public function members(Project $project, Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewMembers', $project);

        $members = $this->projectService->getMembers(
            project: $project,
            perPage: (int) $request->input('per_page', 15),
        );

        return UserResource::collection($members);
    }

    /**
     * Assign user to project.
     */
    public function assignUser(AssignUserRequest $request, Project $project): JsonResponse
    {
        $user = User::findOrFail($request->input('user_id'));

        try {
            $this->projectService->assignUser(
                project: $project,
                user: $user,
                assignedBy: $request->user(),
            );

            return response()->json([
                'message' => __('project.user_assigned_successfully'),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove user from project.
     */
    public function removeUser(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('removeMembers', $project);

        try {
            $this->projectService->removeUser(
                project: $project,
                user: $user,
            );

            return response()->json([
                'message' => __('project.user_removed_successfully'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
