<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_list_orders(): void
    {
        Order::factory()->withItems(2)->create();
        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_order(): void
    {
        $product = Product::factory()->create(['price' => 100]);
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'pending',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1]
            ],
        ];

        $response = $this->postJson('/api/admin/orders/create', $payload);
        $response->assertStatus(200)->assertJsonStructure(['data' => ['id']]);
    }

    public function test_admin_can_update_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->putJson('/api/admin/orders/' . $order->id, [
            'status' => 'completed',
        ]);
        $response->assertStatus(200);
    }

    public function test_admin_can_delete_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson('/api/admin/orders/' . $order->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_admin_can_add_item_to_order(): void
    {
        $order = Order::factory()->withItems(3)->create();
        $product = Product::factory()->create(['price' => 50]);

        $response = $this->postJson('/api/admin/orders/' . $order->id . '/add-item', [
            'product_id' => $product->id,
            'qty' => 2,
        ]);
        $response->assertStatus(200);
    }

    public function test_admin_can_update_order_item(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 20]);
        // Assume an item exists for update
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
        ]);

        $response = $this->postJson('/api/admin/orders/' . $order->id . '/update-item/' . $item->id, [
            'qty' => 3,
        ]);
        $response->assertStatus(200);
    }

    public function test_admin_can_remove_order_item(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 10]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
        ]);

        $response = $this->deleteJson('/api/admin/orders/' . $order->id . '/remove-item/' . $item->id);
        $response->assertStatus(200);
    }
}
