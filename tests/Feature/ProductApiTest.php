<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    private function createCurrency(string $code = 'ARS'): Currency
    {
        return Currency::create([
            'code' => $code,
            'name' => $code === 'USD' ? 'US Dollar' : 'Argentine Peso',
            'symbol' => $code === 'USD' ? 'US$' : '$',
            'exchange_rate' => $code === 'USD' ? 1 : 1500,
        ]);
    }

    public function test_product_endpoints_require_authentication(): void
    {
        $currency = $this->createCurrency();
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 100,
            'currency_id' => $currency->id,
        ]);

        $this->getJson('/api/products')->assertUnauthorized();
        $this->postJson('/api/products', [])->assertUnauthorized();
        $this->getJson("/api/products/{$product->id}")->assertUnauthorized();
        $this->putJson("/api/products/{$product->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/products/{$product->id}")->assertUnauthorized();
        $this->getJson("/api/products/{$product->id}/prices")->assertUnauthorized();
        $this->postJson("/api/products/{$product->id}/prices", [])->assertUnauthorized();
    }

    public function test_products_can_be_listed_as_json(): void
    {
        $this->authenticate();

        $currency = $this->createCurrency();

        Product::create([
            'name' => 'Teclado',
            'description' => 'Teclado mecánico',
            'price' => 100,
            'currency_id' => $currency->id,
            'tax_cost' => 21,
            'manufacturing_cost' => 35,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Teclado',
                'description' => 'Teclado mecánico',
                'currency_id' => $currency->id,
                'tax_cost' => '21.00',
                'manufacturing_cost' => '35.00',
            ]);
    }

    public function test_a_product_can_be_created(): void
    {
        $this->authenticate();

        $currency = $this->createCurrency();

        $this->postJson('/api/products', [
            'name' => 'Mouse',
            'description' => 'Mouse inalámbrico',
            'price' => 50,
            'currency_id' => $currency->id,
            'tax_cost' => 10,
            'manufacturing_cost' => 15,
        ])
            ->assertCreated()
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

    public function test_a_product_can_be_viewed_with_its_relations(): void
    {
        $this->authenticate();

        $currency = $this->createCurrency();

        $product = Product::create([
            'name' => 'Monitor',
            'price' => 200,
            'currency_id' => $currency->id,
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Monitor',
                'code' => 'ARS',
            ]);
    }

    public function test_a_product_can_be_updated(): void
    {
        $this->authenticate();

        $currency = $this->createCurrency();

        $product = Product::create([
            'name' => 'Mouse',
            'price' => 50,
            'currency_id' => $currency->id,
            'tax_cost' => 10,
            'manufacturing_cost' => 15,
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Mouse Gamer',
            'price' => 80,
        ])
            ->assertOk()
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

    public function test_a_product_can_be_deleted_with_its_price_history(): void
    {
        $this->authenticate();

        $ars = $this->createCurrency();
        $usd = $this->createCurrency('USD');

        $product = Product::create([
            'name' => 'Teclado',
            'price' => 100,
            'currency_id' => $ars->id,
        ]);

        $price = $product->prices()->create([
            'price' => 200,
            'currency_id' => $usd->id,
        ]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJson([
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);

        $this->assertDatabaseMissing('product_prices', [
            'id' => $price->id,
        ]);
    }

    public function test_a_product_can_have_prices_in_different_currencies(): void
    {
        $this->authenticate();

        $ars = $this->createCurrency();
        $usd = $this->createCurrency('USD');

        $product = Product::create([
            'name' => 'Teclado',
            'price' => 100,
            'currency_id' => $ars->id,
        ]);

        $this->postJson("/api/products/{$product->id}/prices", [
            'price' => 200,
            'currency_id' => $usd->id,
        ])
            ->assertCreated()
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
        $this->authenticate();

        $ars = $this->createCurrency();
        $usd = $this->createCurrency('USD');

        $product = Product::create([
            'name' => 'Monitor',
            'price' => 100,
            'currency_id' => $ars->id,
        ]);

        $product->prices()->create([
            'price' => 200,
            'currency_id' => $usd->id,
        ]);

        $this->getJson("/api/products/{$product->id}/prices")
            ->assertOk()
            ->assertJsonFragment([
                'price' => '200.00',
                'currency_id' => $usd->id,
            ]);
    }

    public function test_product_creation_requires_a_name(): void
    {
        $this->authenticate();

        $currency = $this->createCurrency();

        $this->postJson('/api/products', [
            'description' => 'Producto sin nombre',
            'price' => 100,
            'currency_id' => $currency->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
