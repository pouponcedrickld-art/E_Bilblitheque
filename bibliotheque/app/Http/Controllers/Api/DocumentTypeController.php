<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DocumentTypeController extends Controller
{
    public function index()
    {
        return Cache::remember('document_types', 86400, function () {
            return DocumentType::orderBy('label')->get();
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Cache::forget('document_types');

        return DocumentType::create($validated);
    }

    public function show(DocumentType $documentType)
    {
        return $documentType;
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name,' . $documentType->id,
            'label' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $documentType->update($validated);

        Cache::forget('document_types');

        return $documentType;
    }

    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();

        Cache::forget('document_types');

        return response()->noContent();
    }
}
