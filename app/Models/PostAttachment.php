<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'filename',
        'original_filename',
        'file_size',
        'mime_type',
        'extension',
        'storage_path'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }

    public function deleteFile()
    {
        Storage::disk('s3')->delete('post-attachments/' . $this->filename);
    }
}
