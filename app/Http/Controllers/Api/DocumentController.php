<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['uploadedBy', 'contact', 'account', 'deal']);
        if ($request->account_id) $query->where('account_id', $request->account_id);
        if ($request->contact_id) $query->where('contact_id', $request->contact_id);
        if ($request->deal_id)    $query->where('deal_id', $request->deal_id);
        
        // Folder navigation
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        return response()->json($query->orderBy('type')->latest()->paginate(100)); // Folders first
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'       => 'required|file|max:20480', // 20MB max
            'name'       => 'required|string|max:255',
            'category'   => 'nullable|string|max:50',
            'contact_id' => 'nullable|exists:contacts,id',
            'account_id' => 'nullable|exists:accounts,id',
            'deal_id'    => 'nullable|exists:deals,id',
            'notes'      => 'nullable|string',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('documents', 'public');

        $document = Document::create([
            'name'        => $request->name,
            'type'        => 'file',
            'parent_id'   => $request->parent_id,
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
            'category'    => $request->category,
            'contact_id'  => $request->contact_id,
            'account_id'  => $request->account_id,
            'deal_id'     => $request->deal_id,
            'uploaded_by' => auth()->id() ?? 1,
            'notes'       => $request->notes,
        ]);

        return response()->json($document->load('uploadedBy'), 201);
    }

    public function show(Document $document)
    {
        return response()->json($document->load(['uploadedBy', 'contact', 'account', 'deal']));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:50',
            'notes'    => 'nullable|string',
        ]);
        $document->update($validated);
        return response()->json($document);
    }

    public function storeFolder(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $folder = Document::create([
            'name'        => $request->name,
            'type'        => 'folder',
            'parent_id'   => $request->parent_id,
            'uploaded_by' => auth()->id() ?? 1
        ]);
        return response()->json($folder, 201);
    }

    public function move(Request $request, Document $document)
    {
        $request->validate(['parent_id' => 'nullable|exists:documents,id']);
        $document->update(['parent_id' => $request->parent_id]);
        return response()->json($document);
    }

    public function destroy(Document $document)
    {
        if ($document->type === 'file' && $document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return response()->noContent();
    }
}
