<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->image_url,
            'is_available' => $this->is_available,
            'category' => $this->whenLoaded('category', fn (): ?array => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
