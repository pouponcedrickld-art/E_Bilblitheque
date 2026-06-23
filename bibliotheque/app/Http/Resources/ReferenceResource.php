<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\KeywordResource;

class ReferenceResource extends JsonResource
{
    // Expose les données d'une référence bibliographique
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'abstract' => $this->abstract,
            'isbn' => $this->isbn,
            'publication_year' => $this->publication_year,
            'language' => $this->language,
            'document_type_id' => $this->document_type_id,
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'publisher' => new PublisherResource($this->whenLoaded('publisher')),
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
            'keywords' => KeywordResource::collection($this->whenLoaded('keywords')),
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'cover_image' => $this->cover_image,
            'cover_url' => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path ? Storage::disk('public')->url($this->file_path) : null,
            'pages' => $this->pages,
            'download_count' => $this->download_count,
            'view_count' => $this->view_count,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
