<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\ApiResourceCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    /**
     * Get comments for a project.
     */
    public function projectComments(Request $request, Project $project): ApiResourceCollection
    {
        $this->authorize('view', $project);

        $comments = $this->commentService->getProjectComments(
            project: $project,
            perPage: (int) $request->input('per_page', 15),
        );

        return (new ApiResourceCollection($comments))
            ->setResourceClass(CommentResource::class)
            ->withMessage(__('comment.list_retrieved'));
    }

    /**
     * Add comment to project.
     */
    public function storeProjectComment(StoreCommentRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $comment = $this->commentService->create(
            commentable: $project,
            data: $request->validated(),
            creator: $request->user(),
        );

        return (new CommentResource($comment->load('user')))
            ->withMessage(__('comment.created_successfully'))
            ->withStatusCode(201)
            ->toResponse($request);
    }

    /**
     * Get comments for a task.
     */
    public function taskComments(Request $request, Task $task): ApiResourceCollection
    {
        $this->authorize('view', $task);

        $comments = $this->commentService->getTaskComments(
            task: $task,
            perPage: (int) $request->input('per_page', 15),
        );

        return (new ApiResourceCollection($comments))
            ->setResourceClass(CommentResource::class)
            ->withMessage(__('comment.list_retrieved'));
    }

    /**
     * Add comment to task.
     */
    public function storeTaskComment(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $comment = $this->commentService->create(
            commentable: $task,
            data: $request->validated(),
            creator: $request->user(),
        );

        return (new CommentResource($comment->load('user')))
            ->withMessage(__('comment.created_successfully'))
            ->withStatusCode(201)
            ->toResponse($request);
    }

    /**
     * Update a comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $updated = $this->commentService->update(
            comment: $comment,
            data: $request->validated(),
        );

        return (new CommentResource($updated->load('user')))
            ->withMessage(__('comment.updated_successfully'))
            ->toResponse($request);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete(
            comment: $comment,
            deletedBy: $request->user(),
        );

        return ApiResponse::success(
            message: __('comment.deleted_successfully')
        );
    }
}
