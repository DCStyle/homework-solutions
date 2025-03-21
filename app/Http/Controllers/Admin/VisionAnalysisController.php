<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VisionAnalysisController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Show vision analysis form
     */
    public function index()
    {
        return view('admin.ai-dashboard.vision-analysis');
    }

    /**
     * Process image for analysis
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
            'prompt' => 'required|string|max:1000',
            'model' => 'required|string',
        ]);

        try {
            // Store the uploaded image
            $imagePath = $request->file('image')->store('vision-analysis', 's3');
            $imageUrl = url(Storage::url($imagePath));

            // Determine model settings
            $useDeepseek = str_starts_with($request->input('model'), 'deepseek');
            $options = [
                'use_deepseek' => $useDeepseek,
                'temperature' => (float)$request->input('temperature', 0.7),
            ];

            if ($useDeepseek) {
                $options['deepseek_model'] = $request->input('deepseek_model', 'deepseek-vision');
                $options['system_message'] = $request->input('system_message');
            }

            // Call AI Vision API
            $result = $this->aiService->analyzeImage($imageUrl, $request->prompt, $options);

            return view('admin.ai-dashboard.vision-results', [
                'imageUrl' => $imageUrl,
                'prompt' => $request->prompt,
                'result' => $result,
                'model' => $request->input('model'),
            ]);
        } catch (\Exception $e) {
            Log::error('Vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'api_error' => 'Vision analysis failed: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * API endpoint for image analysis (for AJAX requests)
     */
    public function analyzeApi(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
            'prompt' => 'required|string|max:1000',
            'model' => 'required|string',
        ]);

        try {
            // Store the uploaded image
            $imagePath = $request->file('image')->store('vision-analysis', 's3');
            $imageUrl = url(Storage::url($imagePath));

            // Determine model settings
            $useDeepseek = str_starts_with($request->input('model'), 'deepseek');
            $options = [
                'use_deepseek' => $useDeepseek,
                'temperature' => (float)$request->input('temperature', 0.7),
            ];

            if ($useDeepseek) {
                $options['deepseek_model'] = $request->input('deepseek_model', 'deepseek-vision');
                $options['system_message'] = $request->input('system_message');
            }

            // Call AI Vision API
            $result = $this->aiService->analyzeImage($imageUrl, $request->prompt, $options);

            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
                'prompt' => $request->prompt,
                'result' => $result,
                'model' => $request->input('model'),
            ]);
        } catch (\Exception $e) {
            Log::error('Vision analysis API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Vision analysis failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
