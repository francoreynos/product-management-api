<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('currency')->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'tax_cost' => 'nullable|numeric|min:0',
            'manufacturing_cost' => 'nullable|numeric|min:0',
        ]);

        $validated['tax_cost'] = $validated['tax_cost'] ?? 0;
	$validated['manufacturing_cost'] = $validated['manufacturing_cost'] ?? 0;

	$product = Product::create($validated);

        return response()->json($product->load('currency'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('currency', 'prices.currency'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'tax_cost' => 'sometimes|numeric|min:0',
            'manufacturing_cost' => 'sometimes|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json($product->load('currency'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
