<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    /**
     * Upload and store an attachment.
     */
    public function upload(
        UploadedFile $file,
        string $attachableType,
        ?string $attachableId,
        User $user
    ): Attachment {
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
        $uniqueFileName = Str::slug($fileName).'-'.Str::random(8).'.'.$extension;

        // Store file in attachments directory
        $path = $file->storeAs('attachments', $uniqueFileName, 'private');

        // Create attachment record
        $attachment = Attachment::create([
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return $attachment;
    }

    /**
     * Download an attachment.
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        // Check if file exists
        if (! Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        // Return file download response
        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Delete an attachment.
     */
    public function delete(Attachment $attachment, User $deletedBy): void
    {
        // Delete physical file
        if (Storage::disk('private')->exists($attachment->file_path)) {
            Storage::disk('private')->delete($attachment->file_path);
        }

        // Soft delete record
        $attachment->update(['deleted_by' => $deletedBy->id]);
        $attachment->delete();
    }
}
