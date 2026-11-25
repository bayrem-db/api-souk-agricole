<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
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
            'titre' => $this->titre,
            'description' => $this->description,
            'prix' => $this->prix,
            'quantite_kg' => $this->quantite_kg,
            'date_recolte' => $this->date_recolte?->toIso8601String(),
            'disponible' => $this->disponible,
            'user' => new UserResource($this->whenLoaded('user')),
            'produit' => new ProduitResource($this->whenLoaded('produit')),
            'regions' => RegionResource::collection($this->whenLoaded('regions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
