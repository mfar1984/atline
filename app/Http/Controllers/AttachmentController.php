<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function download(Attachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    public function destroy(Attachment $attachment)
    {
        $this->attachmentService->delete($attachment);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
