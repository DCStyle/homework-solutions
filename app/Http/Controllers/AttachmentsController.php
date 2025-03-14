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
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $extension = strtolower($attachment->extension);

        if (!in_array($extension, $allowedExtensions)) {
            abort(400, 'Only PDF, DOC, and DOCX files can be previewed');
        }

        $path = 'post-attachments/' . $attachment->filename;

        try {
            // Generate a temporary URL that expires in 5 minutes
            $url = Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes(5),
                [
                    'ResponseContentType' => $attachment->mime_type
                ]
            );

            // For Word documents, return file information as JSON
            if (in_array($extension, ['doc', 'docx'])) {
                return response()->json([
                    'url' => $url,
                    'filename' => $attachment->original_filename,
                    'type' => 'office'
                ]);
            }

            // For PDFs, redirect as before
            return redirect()->away($url);
        } catch (Exception $e) {
            \Log::error('File preview failed: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);
            abort(500, 'Error previewing file');
        }
    }
}
