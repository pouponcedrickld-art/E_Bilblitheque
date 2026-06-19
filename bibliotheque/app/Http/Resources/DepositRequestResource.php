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
            'description' => $this->description,
            'status' => $this->status,
            'applicant' => new UserResource($this->whenLoaded('applicant')),
            'assigned_manager' => new UserResource($this->whenLoaded('assignedManager')),
            'reviews' => DepositRequestReviewResource::collection($this->whenLoaded('reviews')),
            'proposed_file' => $this->proposed_file,
            'proposed_file_url' => $this->proposed_file ? Storage::disk('public')->url($this->proposed_file) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}