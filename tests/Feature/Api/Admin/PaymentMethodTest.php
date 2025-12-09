<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
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
    public function can_list_payment_methods()
    {
        PaymentMethod::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/payment-methods');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_create_payment_method()
    {
        $payload = PaymentMethod::factory()->make()->toArray();

        $response = $this->postJson('/api/admin/payment-methods', $payload);

        $response->assertStatus(200);
    }

    /** @test */
    public function can_update_payment_method()
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = array_merge($paymentMethod->toArray(), ['name' => 'Updated Name']);

        $response = $this->putJson("/api/admin/payment-methods/{$paymentMethod->id}", $payload);
        $response->assertStatus(200);
    }

    /** @test */
    public function can_delete_payment_method()
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->deleteJson("/api/admin/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
