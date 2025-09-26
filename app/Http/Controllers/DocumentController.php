<?php

namespace App\Http\Controllers;

use App\Services\Core\UnifiedDocumentService;
use Illuminate\Http\Request;
use App\Models\Document;

class DocumentController extends Controller
{
    private $documentService;
    
    public function __construct(UnifiedDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'context' => 'required|string',
            'owner_id' => 'required|integer'
        ]);
        
        try {
            $document = $this->documentService->store(
                $request->file('file'),
                $request->context,
                $request->owner_id,
                [
                    'category' => $request->category ?? null,
                    'requires_verification' => $request->requires_verification ?? false
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document_id' => $document->id,
                'hash' => $document->hash
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function list(Request $request)
    {
        $documents = Document::with('relationships')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($documents);
    }
}