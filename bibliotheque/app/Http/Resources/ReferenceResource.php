<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferenceResource extends JsonResource
{
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
            'document_type' => $this->document_type,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'publisher' => new PublisherResource($this->whenLoaded('publisher')),
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
            'keywords' => $this->whenLoaded('keywords', fn() => $this->keywords->pluck('keyword')),
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'cover_image' => $this->cover_image,
            'file_path' => $this->file_path,
            'pages' => $this->pages,
            'download_count' => $this->download_count,
            'view_count' => $this->view_count,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}