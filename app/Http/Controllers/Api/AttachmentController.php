<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AttachmentService $attachmentService,
    ) {}

    /**
     * Upload an attachment to a resource (project, task, comment, user).
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        // Get the full model class name that was prepared in the request
        $fullClassName = $request->input('attachable_type_full') ?? $request->input('attachable_type');

        $attachment = $this->attachmentService->upload(
            file: $request->file('file'),
            attachableType: $fullClassName,
            attachableId: $request->input('attachable_id'),
            user: $request->user(),
        );

        return (new AttachmentResource($attachment->load(['user', 'attachable'])))
            ->withMessage(__('attachment.uploaded_successfully'))
            ->withStatusCode(HttpResponse::HTTP_CREATED)
            ->toResponse($request);
    }

    /**
     * Download an attachment with authorization check.
     */
    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        return $this->attachmentService->download($attachment);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Request $request, Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        $this->attachmentService->delete(
            attachment: $attachment,
            deletedBy: $request->user(),
        );

        return ApiResponse::success(
            message: __('attachment.deleted_successfully')
        );
    }
}
