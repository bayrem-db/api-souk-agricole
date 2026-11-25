<?php

use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\ProduitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('annonces', AnnonceController::class);
Route::apiResource('produits', ProduitController::class)->only(['index', 'show']);
