<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_listed_as_json(): void
    {
        $currency = Currency::create([
            'code' => 'ARS',
            'name' => 'Argentine Peso',
            'symbol' => '$',
        ]);

        Product::create([
            'name' => 'Teclado',
            'description' => 'Teclado mecánico',
            'price' => 100,
            'currency_id' => $currency->id,
            'tax_cost' => 21,
            'manufacturing_cost' => 35,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Teclado',
                'description' => 'Teclado mecánico',
                'currency_id' => $currency->id,
                'tax_cost' => '21.00',
                'manufacturing_cost' => '35.00',
            ]);
    }

    public function test_products_can_be_created(): void
{
    $currency = Currency::create([
        'code' => 'ARS',
        'name' => 'Argentine Peso',
        'symbol' => '$',
    ]);

    $response = $this->postJson('/api/products', [
        'name' => 'Mouse',
        'description' => 'Mouse inalámbrico',
        'price' => 50,
        'currency_id' => $currency->id,
        'tax_cost' => 10,
        'manufacturing_cost' => 15,
    ]);

    $response->assertCreated()
        ->assertJsonFragment([
            'name' => 'Mouse',
            'description' => 'Mouse inalámbrico',
            'currency_id' => $currency->id,
            'tax_cost' => '10.00',
            'manufacturing_cost' => '15.00',
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Mouse',
        'currency_id' => $currency->id,
    ]);
}


    public function test_products_can_be_updated(): void
{
    $currency = Currency::create([
        'code' => 'ARS',
        'name' => 'Argentine Peso',
        'symbol' => '$',
    ]);

    $product = Product::create([
        'name' => 'Mouse',
        'description' => 'Mouse inalámbrico',
        'price' => 50,
        'currency_id' => $currency->id,
        'tax_cost' => 10,
        'manufacturing_cost' => 15,
    ]);

    $response = $this->putJson("/api/products/{$product->id}", [
        'name' => 'Mouse Gamer',
        'price' => 80,
    ]);

    $response->assertOk()
        ->assertJsonFragment([
            'name' => 'Mouse Gamer',
            'price' => '80.00',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Mouse Gamer',
        'price' => 80,
    ]);
}


public function test_products_can_be_deleted(): void
{
    $currency = Currency::create([
        'code' => 'ARS',
        'name' => 'Argentine Peso',
        'symbol' => '$',
    ]);

    $product = Product::create([
        'name' => 'Teclado',
        'description' => 'Teclado mecánico',
        'price' => 100,
        'currency_id' => $currency->id,
        'tax_cost' => 21,
        'manufacturing_cost' => 35,
    ]);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'Product deleted successfully',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
}


public function test_products_can_have_prices_in_different_currencies(): void
{
    $ars = Currency::create([
        'code' => 'ARS',
        'name' => 'Argentine Peso',
        'symbol' => '$',
    ]);

    $usd = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => 'US$',
    ]);

    $product = Product::create([
        'name' => 'Teclado',
        'description' => 'Teclado mecánico',
        'price' => 100,
        'currency_id' => $ars->id,
        'tax_cost' => 21,
        'manufacturing_cost' => 35,
    ]);

    $response = $this->postJson("/api/products/{$product->id}/prices", [
        'price' => 200,
        'currency_id' => $usd->id,
    ]);

    $response->assertCreated()
        ->assertJsonFragment([
            'price' => '200.00',
            'currency_id' => $usd->id,
        ]);

    $this->assertDatabaseHas('product_prices', [
        'product_id' => $product->id,
        'price' => 200,
        'currency_id' => $usd->id,
    ]);
}


public function test_product_price_history_can_be_listed(): void
{
    $ars = Currency::create([
        'code' => 'ARS',
        'name' => 'Argentine Peso',
        'symbol' => '$',
    ]);

    $usd = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => 'US$',
    ]);

    $product = Product::create([
        'name' => 'Monitor',
        'description' => 'Monitor 24 pulgadas',
        'price' => 100,
        'currency_id' => $ars->id,
        'tax_cost' => 21,
        'manufacturing_cost' => 35,
    ]);

    $product->prices()->create([
        'price' => 200,
        'currency_id' => $usd->id,
    ]);

    $response = $this->getJson("/api/products/{$product->id}/prices");

    $response->assertOk()
        ->assertJsonFragment([
            'price' => '200.00',
            'currency_id' => $usd->id,
        ]);
}


public function test_product_creation_requires_a_name(): void
{
    $response = $this->postJson('/api/products', [
        'description' => 'Producto sin nombre',
        'price' => 100,
        'currency_id' => 1,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
}

}