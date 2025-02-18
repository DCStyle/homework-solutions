<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            // Max file size is 100MB
            'file' => 'required|file|max:102400',
        ]);

        $file = $request->file('file');
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Store the file
        $file->storeAs('post-attachments', $filename, 's3');

        // Create temporary attachment record
        $attachment = PostAttachment::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension()
        ]);

        return response()->json([
            'id' => $attachment->id,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize()
        ]);
    }

    public function destroy($id)
    {
        $attachment = PostAttachment::findOrFail($id);

        // Delete the file
        Storage::disk('public')->delete('post-attachments/' . $attachment->filename);

        // Delete the record
        $attachment->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}
