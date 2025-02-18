<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    protected $validationRules = [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ];

    public function upload(Request $request)
    {
        try {
            $request->validate($this->validationRules);

            $path = $this->storeImage($request->file('image'));

            // Create the image record
            $image = Image::create([
                'path' => $path,
                'name' => $request->file('image')->getClientOriginalName(),
                'alt' => $request->file('image')->getClientOriginalName(),
                'imageable_type' => '',
                'imageable_id' => 0
            ]);

            return response()->json([
                'success' => true,
                'url' => Storage::disk('s3')->url($path),
                'image_id' => $image->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function attach(Request $request)
    {
        try {
            $request->validate([
                'model_type' => 'required|string',
                'model_id' => 'required|integer',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Get the model class
            $modelClass = "App\\Models\\" . Str::studly($request->model_type);

            if (!class_exists($modelClass)) {
                throw new \Exception("Model not found");
            }

            // Find the model instance
            $model = $modelClass::findOrFail($request->model_id);

            // Store the image
            $path = $this->storeImage($request->file('image'));

            // Create and attach the image
            $image = new Image([
                'path' => $path,
                'name' => $request->file('image')->getClientOriginalName(),
                'alt' => $request->get('alt'),
                'imageable_type' => $modelClass,
                'imageable_id' => $model->id
            ]);

            $model->images()->save($image);

            return response()->json([
                'success' => true,
                'image' => $image,
                'url' => Storage::disk('s3')->url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Image $image)
    {
        try {
            // Delete the file from S3
            if (Storage::disk('s3')->exists($image->path)) {
                Storage::disk('s3')->delete($image->path);
            }

            // Delete the record
            $image->delete();

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    protected function storeImage($file)
    {
        // Generate a unique folder name based on date
        $folder = 'uploads/images/' . date('Y/m/d');

        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store the file in S3
        $path = $file->storeAs($folder, $filename, 's3');

        // Make the file publicly accessible
        Storage::disk('s3')->setVisibility($path, 'public');

        return $path;
    }
}
