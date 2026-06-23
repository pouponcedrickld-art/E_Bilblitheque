<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        return DocumentType::orderBy('label')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

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
        return $documentType;
    }

    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();
        return response()->noContent();
    }
}
