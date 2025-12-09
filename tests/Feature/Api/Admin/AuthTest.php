<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $admin = Admin::factory()->create([
            'password' => '12345678'
        ]);

        $response = $this->post('/api/admin/login', [
            'email' => $admin->email,
            'password' => '12345678'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    /** @test */
    public function authenticated_admin_can_logout()
    {
        $admin = Admin::factory()->create();

        $token = JWTAuth::fromUser($admin, ['guard' => 'admin']);

        $response = $this->withHeader('Authorization', "Bearer $token")->post('/api/admin/logout');

        $response->assertStatus(200);
    }
}
