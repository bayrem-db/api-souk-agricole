<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterAnnonceRequest;
use App\Http\Resources\AnnonceResource;
use App\Models\Annonce;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterAnnonceRequest $request): AnonymousResourceCollection
    {
        $query = Annonce::query()
            ->with(['user', 'produit', 'regions'])
            ->when($request->validated('produit'), function ($query, string $slug) {
                $query->whereHas('produit', fn ($query) => $query->where('slug', $slug));
            })
            ->latest();

        return AnnonceResource::collection($query->paginate(10));
    }

    /**
     * Display the specified resource.
     */
    public function show(Annonce $annonce): AnnonceResource
    {
        return new AnnonceResource($annonce->load(['user', 'produit', 'regions']));
    }
}
