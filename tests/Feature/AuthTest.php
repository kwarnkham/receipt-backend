<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
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
}
