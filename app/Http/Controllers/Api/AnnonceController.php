<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterAnnonceRequest;
use App\Http\Requests\StoreAnnonceRequest;
use App\Http\Requests\UpdateAnnonceRequest;
use App\Http\Resources\AnnonceResource;
use App\Models\Annonce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnonceRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['regions']);

        $annonce = Annonce::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);
        $annonce->regions()->sync($request->validated('regions'));

        return (new AnnonceResource($annonce->load(['user', 'produit', 'regions'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnonceRequest $request, Annonce $annonce): AnnonceResource
    {
        $data = $request->validated();
        $regions = $data['regions'];
        unset($data['regions']);

        $annonce->update($data);
        $annonce->regions()->sync($regions);

        return new AnnonceResource($annonce->load(['user', 'produit', 'regions']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Annonce $annonce): Response
    {
        Gate::authorize('delete', $annonce);

        $annonce->delete();

        return response()->noContent();
    }
}
