<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProduitResource;
use App\Models\Produit;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $produits = Produit::withCount('annonces')->orderBy('name')->get();

        return ProduitResource::collection($produits);
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit): ProduitResource
    {
        return new ProduitResource($produit->loadCount('annonces'));
    }
}
