<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $user2;
    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->admin = User::factory()->has(Role::factory(['name' => 'admin']))->create();
    }
    public function test_login()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'mobile' => $user->mobile,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertTrue($user->id == PersonalAccessToken::first()->tokenable_id);
        $response->assertJson(
            fn (AssertableJson $json) => $json->hasAll(['token', 'user'])
        );
        $token = $response->json()['token'];
        $response = $this->getJson('/api/token', [
            "Authorization" => "Bearer " . $token
        ]);
        $response->assertOk();
    }

    public function test_change_password()
    {
        $newPassword = 'new_password';
        $response = $this->actingAs($this->user)->postJson('api/password/', [
            'password' => 'password',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword
        ]);
        $response->assertOk();
        $response = $this->postJson('api/login', [
            'mobile' => $this->user->mobile,
            'password' => $newPassword
        ]);
        $response->assertOk();
    }
}
