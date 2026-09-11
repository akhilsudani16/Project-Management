<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\ApiResourceCollection;
use App\Http\Resources\TagResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Services\TagService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TagService $tagService,
    ) {}

    /**
     * List tags for an organization.
     */
    public function index(Request $request, Organization $organization): ApiResourceCollection
    {
        $this->authorize('view', $organization);

        $tags = $this->tagService->list(
            organization: $organization,
            perPage: (int) $request->input('per_page', 50),
        );

        return (new ApiResourceCollection($tags))
            ->setResourceClass(TagResource::class)
            ->withMessage(__('tag.list_retrieved'));
    }

    /**
     * Create a new tag.
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->create(
            data: $request->validated(),
            creator: $request->user(),
        );

        return (new TagResource($tag->load('organization')))
            ->withMessage(__('tag.created_successfully'))
            ->withStatusCode(201)
            ->toResponse($request);
    }

    /**
     * Update a tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $updated = $this->tagService->update(
            tag: $tag,
            data: $request->validated(),
        );

        return (new TagResource($updated))
            ->withMessage(__('tag.updated_successfully'))
            ->toResponse($request);
    }

    /**
     * Delete a tag.
     */
    public function destroy(Request $request, Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $this->tagService->delete($tag);

        return ApiResponse::success(
            message: __('tag.deleted_successfully')
        );
    }

    /**
     * Attach tag to task.
     */
    public function attachToTask(Request $request, Tag $tag, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $attached = $this->tagService->attachToTask($tag, $task);

        return ApiResponse::success(
            message: $attached ? __('tag.attached_successfully') : __('tag.already_attached')
        );
    }

    /**
     * Detach tag from task.
     */
    public function detachFromTask(Request $request, Tag $tag, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $this->tagService->detachFromTask($tag, $task);

        return ApiResponse::success(
            message: __('tag.detached_successfully')
        );
    }

    /**
     * Attach tag to project.
     */
    public function attachToProject(Request $request, Tag $tag, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $attached = $this->tagService->attachToProject($tag, $project);

        return ApiResponse::success(
            message: $attached ? __('tag.attached_successfully') : __('tag.already_attached')
        );
    }

    /**
     * Detach tag from project.
     */
    public function detachFromProject(Request $request, Tag $tag, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $this->tagService->detachFromProject($tag, $project);

        return ApiResponse::success(
            message: __('tag.detached_successfully')
        );
    }
}
