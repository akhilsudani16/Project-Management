<?php

declare(strict_types=1);

namespace App\Http\Requests\Attachment;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user can access the attachable resource
        $attachableType = $this->input('attachable_type');
        $attachableId = $this->input('attachable_id');

        if (! $attachableType || ! $attachableId) {
            return true; // Will fail validation
        }

        return match ($attachableType) {
            'App\\Models\\User' => $this->authorizeUserAttachment($attachableId),
            'App\\Models\\Project' => $this->authorizeProjectAttachment($attachableId),
            'App\\Models\\Task' => $this->authorizeTaskAttachment($attachableId),
            'App\\Models\\Comment' => $this->authorizeCommentAttachment($attachableId),
            default => false,
        };
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
            ],
            'attachable_type' => [
                'required',
                'string',
                Rule::in([
                    'App\\Models\\User',
                    'App\\Models\\Project',
                    'App\\Models\\Task',
                    'App\\Models\\Comment',
                ]),
            ],
            'attachable_id' => ['required', 'string', 'uuid'],
        ];
    }

    private function authorizeUserAttachment(string $userId): bool
    {
        // Users can only upload to their own profile
        return $this->user()->id === $userId;
    }

    private function authorizeProjectAttachment(string $projectId): bool
    {
        $project = Project::find($projectId);

        if (! $project) {
            return false;
        }

        // Check if user has access to project
        return $this->user()->can('update', $project);
    }

    private function authorizeTaskAttachment(string $taskId): bool
    {
        $task = Task::find($taskId);

        if (! $task) {
            return false;
        }

        // Check if user has access to task
        return $this->user()->can('update', $task);
    }

    private function authorizeCommentAttachment(string $commentId): bool
    {
        $comment = Comment::find($commentId);

        if (! $comment) {
            return false;
        }

        // Check if user owns the comment or can moderate it
        return $this->user()->can('update', $comment);
    }
}
