<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIApiKey;
use App\Services\AI\AIServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIApiKeyController extends Controller
{
    /**
     * Display a listing of API keys.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $apiKeys = AIApiKey::orderBy('provider')->orderBy('created_at', 'desc')->get();
        $providers = AIServiceFactory::getAvailableProviders();
        
        return view('admin.ai_api_keys.index', compact('apiKeys', 'providers'));
    }

    /**
     * Store a newly created API key in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string',
            'api_key' => 'required|string',
            'email' => 'required|email',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $apiKey = new AIApiKey();
        $apiKey->provider = $request->provider;
        $apiKey->api_key = $request->api_key;
        $apiKey->email = $request->email;
        $apiKey->is_active = $request->filled('is_active');
        $apiKey->save();

        return redirect()->route('admin.ai_api_keys.index')
            ->with('success', 'API key added successfully.');
    }

    /**
     * Update the specified API key in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string',
            'api_key' => 'required|string',
            'email' => 'required|email',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $apiKey = AIApiKey::findOrFail($id);
        $apiKey->provider = $request->provider;
        $apiKey->api_key = $request->api_key;
        $apiKey->email = $request->email;
        $apiKey->is_active = $request->filled('is_active');
        $apiKey->save();

        return redirect()->route('admin.ai_api_keys.index')
            ->with('success', 'API key updated successfully.');
    }

    /**
     * Remove the specified API key from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $apiKey = AIApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()->route('admin.ai_api_keys.index')
            ->with('success', 'API key deleted successfully.');
    }
    
    /**
     * Toggle the active status of an API key.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleActive($id)
    {
        $apiKey = AIApiKey::findOrFail($id);
        $apiKey->is_active = !$apiKey->is_active;
        $apiKey->save();
        
        $status = $apiKey->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.ai_api_keys.index')
            ->with('success', "API key {$status} successfully.");
    }
    
    /**
     * Test an API key to verify it works.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function testKey(Request $request, $id)
    {
        $apiKey = AIApiKey::findOrFail($id);
        
        try {
            // Get service via factory using the API key's provider
            $service = AIServiceFactory::createService($apiKey->provider);
            
            // Simple test prompt
            $testPrompt = "Hello, this is a test to verify that this API key is working.";
            
            // Different default models for different providers
            $model = match($apiKey->provider) {
                'google-gemini' => 'gemini-pro',
                'xai-grok' => 'grok-2',
                'openrouter' => 'x-ai/grok-2',
                default => 'grok-2'
            };
            
            // Attempt to generate a response
            $response = $service->generate($model, $testPrompt, ['max_tokens' => 50]);
            
            // If we get here without exception, the key is working
            return redirect()->route('admin.ai_api_keys.index')
                ->with('success', "API key tested successfully for provider: {$apiKey->provider}");
                
        } catch (\Exception $e) {
            return redirect()->route('admin.ai_api_keys.index')
                ->with('error', "API key test failed: {$e->getMessage()}");
        }
    }
}
