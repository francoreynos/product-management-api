
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;

// Products CRUD
Route::apiResource('products', ProductController::class);

// Product price history
Route::get('/products/{product}/prices', [ProductPriceController::class, 'index']);
Route::post('/products/{product}/prices', [ProductPriceController::class, 'store']);
