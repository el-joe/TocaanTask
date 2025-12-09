<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_create_product()
    {
        $payload = Product::factory()->make()->toArray();

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(200);
    }

    /** @test */
    public function can_update_product()
    {
        $product = Product::factory()->create();

        $payload = array_merge($product->toArray(), ['name' => 'Updated Name']);

        $response = $this->putJson("/api/admin/products/{$product->id}", $payload);
        $response->assertStatus(200);
    }

    /** @test */
    public function can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
