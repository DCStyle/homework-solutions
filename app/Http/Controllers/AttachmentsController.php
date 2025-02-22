<?php

namespace App\Http\Controllers;

use App\Models\PostAttachment;
use Illuminate\Support\Facades\Storage;
use Exception;

class AttachmentsController extends Controller
{
    public function download(PostAttachment $attachment)
    {
        // Show countdown page instead of direct download
        return view('attachments.countdown', compact('attachment'));
    }

    public function processDownload(PostAttachment $attachment)
    {
        $path = 'post-attachments/' . $attachment->filename;

        try {
            // Generate a temporary signed URL
            $url = Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes(5),
                [
                    'ResponseContentDisposition' => 'attachment; filename="' . $attachment->original_filename . '"',
                    'ResponseContentType' => $attachment->mime_type,
                ]
            );

            return redirect()->away($url);
        } catch (Exception $e) {
            \Log::error('File download failed: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);
            abort(500, 'Error downloading file');
        }
    }

    public function preview(PostAttachment $attachment)
    {
        if (strtolower($attachment->extension) !== 'pdf') {
            abort(400, 'Only PDF files can be previewed');
        }

        $path = 'post-attachments/' . $attachment->filename;

        try {
            // Generate a temporary URL that expires in 5 minutes
            $url = Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes(5),
                [
                    'ResponseContentType' => 'application/pdf'
                ]
            );

            return redirect()->away($url);
        } catch (Exception $e) {
            \Log::error('PDF preview failed: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);
            abort(500, 'Error previewing file');
        }
    }
}
