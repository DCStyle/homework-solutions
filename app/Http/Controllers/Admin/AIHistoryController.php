<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIGenerationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIHistoryController extends Controller
{
    /**
     * Display a listing of AI generation history.
     */
    public function index(Request $request)
    {
        $contentType = $request->input('content_type');
        $status = $request->input('status');
        $search = $request->input('search');
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        
        $query = AIGenerationHistory::query()
            ->with('user')
            ->orderBy($sortBy, $sortOrder);
            
        // Filter by content type if specified
        if ($contentType) {
            $query->where('content_type', $contentType);
        }
        
        // Filter by status if specified
        if ($status) {
            $query->where('status', $status);
        }
        
        // Filter by search term if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('prompt_text', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Show all records as this controller is already protected by admin middleware
        // (Admin middleware provides access to this controller, so permissions are already checked)
        
        $histories = $query->paginate(10);
        
        // Get distinct content types for filtering
        $contentTypes = AIGenerationHistory::distinct('content_type')
            ->pluck('content_type')
            ->toArray();
            
        // Get distinct statuses for filtering
        $statuses = AIGenerationHistory::distinct('status')
            ->pluck('status')
            ->toArray();
            
        return view('admin.ai-history.index', compact(
            'histories', 
            'contentTypes', 
            'statuses', 
            'contentType', 
            'status', 
            'search',
            'sortBy',
            'sortOrder'
        ));
    }
    
    /**
     * Display the specified AI generation history.
     */
    public function show(string $id)
    {
        $history = AIGenerationHistory::with('user')->findOrFail($id);
        
        // Only allow viewing if the user is the owner (admin access is handled by middleware)
        if ($history->user_id !== Auth::id()) {
            // For security, users can only see their own history unless they have admin access
            // Admin middleware already handles the admin check in the routes
            // But we need this check to restrict regular users to only their records
            abort(403, 'Unauthorized action.');
        }
        
        // Get related items based on content_type
        $relatedItems = $this->getRelatedItems($history);
        
        return view('admin.ai-history.show', compact('history', 'relatedItems'));
    }
    
    /**
     * Download a CSV report of the generation results.
     */
    public function downloadReport(string $id)
    {
        $history = AIGenerationHistory::with('user')->findOrFail($id);
        
        // Only allow downloading if the user is the owner (admin access is handled by middleware)
        if ($history->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Generate CSV filename
        $filename = 'ai_generation_report_' . $id . '_' . date('Y-m-d') . '.csv';
        
        // Create a streamed response for the CSV
        $response = new StreamedResponse(function() use ($history) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Name',
                'Status',
                'Error',
                'Note'
            ]);
            
            // Add data rows
            $processedItems = $history->processed_items ?? [];
            foreach ($processedItems as $item) {
                fputcsv($handle, [
                    $item['id'] ?? '',
                    $item['name'] ?? '',
                    $item['status'] ?? '',
                    $item['error'] ?? '',
                    $item['note'] ?? ''
                ]);
            }
            
            fclose($handle);
        });
        
        // Set response headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
    
    /**
     * Get items related to this generation history based on content type.
     */
    private function getRelatedItems(AIGenerationHistory $history)
    {
        $processedItemIds = collect($history->processed_items ?? [])->pluck('id')->toArray();
        
        switch ($history->content_type) {
            case 'posts':
                return \App\Models\Post::whereIn('id', $processedItemIds)->get();
            
            case 'chapters':
                return \App\Models\BookChapter::whereIn('id', $processedItemIds)->get();
            
            case 'books':
                return \App\Models\Book::whereIn('id', $processedItemIds)->get();
            
            case 'book_groups':
                return \App\Models\BookGroup::whereIn('id', $processedItemIds)->get();
            
            default:
                return collect();
        }
    }
}
