<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserTest extends TestCase
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
    public function can_list_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_create_user()
    {
        $payload = User::factory()->make()->toArray();
        $payload['password'] = '123456';

        $response = $this->postJson('/api/admin/users', $payload);

        $response->assertStatus(200);
    }

    /** @test */
    public function can_update_user()
    {
        $user = User::factory()->create();

        $payload = array_merge($user->toArray(), ['name' => 'Updated Name']);

        $response = $this->putJson("/api/admin/users/{$user->id}", $payload);

        $response->assertStatus(200);
    }

    /** @test */
    public function can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
