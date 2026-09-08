<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Organization;
use App\Models\Tag;
use App\Models\Task;
use App\Services\TagService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TagService $tagService,
    ) {}

    /**
     * List tags for an organization.
     */
    public function index(Request $request, Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('view', $organization);

        $tags = $this->tagService->list(
            organization: $organization,
            perPage: (int) $request->input('per_page', 50),
        );

        return TagResource::collection($tags);
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

        return (new TagResource($tag->load(['organization', 'createdBy'])))
            ->additional([
                'message' => __('tag.created_successfully'),
            ])
            ->response()
            ->setStatusCode(201);
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
            ->additional([
                'message' => __('tag.updated_successfully'),
            ])
            ->response();
    }

    /**
     * Delete a tag.
     */
    public function destroy(Request $request, Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $this->tagService->delete($tag);

        return response()->json([
            'message' => __('tag.deleted_successfully'),
        ]);
    }

    /**
     * Attach tag to task.
     */
    public function attachToTask(Request $request, Tag $tag, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $attached = $this->tagService->attachToTask($tag, $task);

        return response()->json([
            'message' => $attached ? __('tag.attached_successfully') : __('tag.already_attached'),
        ]);
    }

    /**
     * Detach tag from task.
     */
    public function detachFromTask(Request $request, Tag $tag, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $this->tagService->detachFromTask($tag, $task);

        return response()->json([
            'message' => __('tag.detached_successfully'),
        ]);
    }
}
