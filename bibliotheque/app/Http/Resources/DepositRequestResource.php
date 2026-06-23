<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DepositRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'abstract' => $this->abstract,
            'description' => $this->description,
            'isbn' => $this->isbn,
            'publication_year' => $this->publication_year,
            'language' => $this->language,
            'document_type_id' => $this->document_type_id,
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'category_id' => $this->category_id,
            'publisher_id' => $this->publisher_id,
            'pages' => $this->pages,
            'cover_image' => $this->cover_image,
            'cover_url' => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
            'proposed_file' => $this->proposed_file,
            'proposed_file_url' => $this->proposed_file ? Storage::disk('public')->url($this->proposed_file) : null,
            'status' => $this->status,
            'applicant' => new UserResource($this->whenLoaded('applicant')),
            'assigned_manager' => new UserResource($this->whenLoaded('assignedManager')),
            'reviews' => DepositRequestReviewResource::collection($this->whenLoaded('reviews')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'publisher' => new PublisherResource($this->whenLoaded('publisher')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
