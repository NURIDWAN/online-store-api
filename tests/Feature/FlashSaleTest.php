<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_never_negative(): void
    {
        // Create product with limited stock
        $product = Product::create([
            'name' => 'Flash Sale Product',
            'price' => 100000,
            'stock' => 1,
        ]);

        // First order should succeed
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Second order should not make stock negative
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Reload product data from database
        $product->refresh();

        // Ensure stock never becomes negative
        $this->assertGreaterThanOrEqual(0, $product->stock);

        // Ensure remaining stock is exactly zero
        $this->assertEquals(0, $product->stock);
    }
}
