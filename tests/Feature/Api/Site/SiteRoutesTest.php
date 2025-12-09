<?php

namespace Tests\Feature\Api\Site;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_products_without_auth(): void
    {
        $response = $this->getJson('/api/site/products');
        $response->assertStatus(200);
    }

    public function test_customer_can_view_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'customer');

        $response = $this->getJson('/api/site/profile');
        $response->assertStatus(200);
    }

    public function test_customer_can_update_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'customer');

        $payload = [
            'name' => 'Updated Name',
            'email' => $user->email,
        ];
        $response = $this->putJson('/api/site/profile', $payload);
        $response->assertStatus(200);
    }

    public function test_customer_can_list_orders(): void
    {
        Order::factory()->withItems(3)->create();
        $user = User::factory()->create();
        $this->actingAs($user, 'customer');

        $response = $this->getJson('/api/site/orders');
        $response->assertStatus(200);
    }

    public function test_customer_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user, 'customer');

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
            ],
        ];
        $response = $this->postJson('/api/site/orders', $payload);
        $response->assertStatus(200);
    }

    public function test_customer_can_view_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $paymentMethod = PaymentMethod::factory()->create();
        Payment::factory()->for($order)->for($paymentMethod)->create();
        $this->actingAs($user, 'customer');

        $response = $this->getJson(uri: '/api/site/payments');
        $response->assertStatus(200);
    }
}
