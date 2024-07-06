<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Models\Brand;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata[0] : json_decode($this->metadata, true);
        $brand = Brand::where('uuid', $metadata['brand'])->first();
        return [
            'uuid' => $this->uuid,
            'category_uuid' => $this->category_uuid,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'metadata' => [
                'brand' => $metadata['brand'],
                'image' => $metadata['image']
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'category' => new CategoryResource($this->category),
            'brand' => new BrandResource($brand)
        ];
    }
}
