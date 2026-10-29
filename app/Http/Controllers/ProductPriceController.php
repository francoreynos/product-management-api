<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function index(Product $product)
    {
        $prices = $product->prices()->with('currency')->get();

        return response()->json($prices);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $price = $product->prices()->create($validated);

        return response()->json($price->load('currency'), 201);
    }
}
