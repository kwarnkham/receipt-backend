<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserTest extends TestCase
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
    public function test_fetching_users()
    {
        $response = $this->actingAs($this->admin)->get('/api/user');

        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 3)
                ->has('data.0', fn ($json) => $json->where('id', 1)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_users_per_page()
    {
        $response = $this->actingAs($this->admin)->get('/api/user?per_page=1');

        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 1)
                ->has('data.0', fn ($json) => $json->where('id', 1)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_user_excluding_admin()
    {
        $response = $this->actingAs($this->admin)->get('/api/user?role=user');
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 2)
                ->has('data.0', fn ($json) => $json->where('id', $this->user->id)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_admin()
    {
        $response = $this->actingAs($this->admin)->get('/api/user?role=admin');
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 1)
                ->has('data.0', fn ($json) => $json->where('id', $this->admin->id)
                    ->where('name', $this->admin->name)->where('mobile', $this->admin->mobile)->etc())
        );

        $response->assertStatus(200);
    }
}
