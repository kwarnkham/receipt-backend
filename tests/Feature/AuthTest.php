<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Role;
use App\Models\Subscription;
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
        Subscription::factory()->create([
            'user_id' => $user->id
        ]);
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

    public function test_only_active_subscriptions_can_login()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'mobile' => $user->mobile,
            'password' => 'password'
        ]);

        $this->assertNotEmpty($response['message']);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        Subscription::factory()->create([
            'user_id' => $user->id
        ]);
        $response = $this->postJson('/api/login', [
            'mobile' => $user->mobile,
            'password' => 'password'
        ]);
        $response->assertOk();
    }

    public function test_limit_login_when_subscription_expired_or_not_active()
    {
        $duration = 30;
        $data = [
            'user_id' => $this->user->id,
            'day' => $duration,
            'price' => 7000,
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $response->assertJson($data);

        $response = $this->postJson('/api/login', [
            'mobile' => $this->user->mobile,
            'password' => 'password'
        ]);
        $response->assertOk();

        $this->travel($duration)->days();
        $response = $this->postJson('/api/login', [
            'mobile' => $this->user->mobile,
            'password' => 'password'
        ]);
        $response->assertOk();
        $this->travel(1)->days();
        $response = $this->postJson('/api/login', [
            'mobile' => $this->user->mobile,
            'password' => 'password'
        ]);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
        $this->assertNotEmpty($response['message']);
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
        Subscription::factory()->create([
            'user_id' => $this->user->id
        ]);
        $response = $this->postJson('api/login', [
            'mobile' => $this->user->mobile,
            'password' => $newPassword
        ]);
        $response->assertOk();
    }
}
