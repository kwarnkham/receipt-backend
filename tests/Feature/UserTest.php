<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
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

    public function test_create_user()
    {
        $data =  [
            'mobile' => '09888',
            'name' => 'user name',
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user', $data);
        $response->assertCreated();
        $this->assertDatabaseCount('users', 4);
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll([
            'id', 'name', 'mobile', 'updated_at', 'created_at',
        ])->where('name', $data['name'])->where('mobile', $data['mobile']));
    }

    public function test_only_admin_can_create_user()
    {
        $data =  [
            'mobile' => '09888',
            'name' => 'user name',
        ];
        $response = $this->actingAs($this->user)->postJson('api/user', $data);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
        $response = $this->actingAs($this->admin)->postJson('api/user', $data);
        $response->assertCreated();
        $this->assertDatabaseCount('users', 4);
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll([
            'id', 'name', 'mobile', 'updated_at', 'created_at',
        ]));
    }

    public function test_fetching_users()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/user');

        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 3)
                ->has('data.0', fn ($json) => $json->where('id', 1)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_users_filtered_by_name()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/user?' . http_build_query(['name' => $this->user->name]));

        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', User::where('name', $this->user->name)->count())
                ->has('data.0', fn ($json) => $json->where('id', $this->user->id)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_users_filtered_by_mobile()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/user?' . http_build_query(['mobile' => $this->user->mobile]));

        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 1)
                ->has('data.0', fn ($json) => $json->where('id', $this->user->id)
                    ->where('name', $this->user->name)->where('mobile', $this->user->mobile)->etc())
        );
        $response->assertStatus(200);
    }

    public function test_fetching_users_per_page()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/user?per_page=1');

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
        $response = $this->actingAs($this->admin)->getJson('/api/user?role=user');
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
        $response = $this->actingAs($this->admin)->getJson('/api/user?role=admin');
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 1)
                ->has('data.0', fn ($json) => $json->where('id', $this->admin->id)
                    ->where('name', $this->admin->name)->where('mobile', $this->admin->mobile)->etc())
        );

        $response->assertStatus(200);
    }

    public function test_fetching_a_user()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/user/' . $this->user->id);
        $response->assertOk();
        $response->assertExactJson($this->user->fresh()->toArray());
        $response->assertJson(['name' => $this->user->name]);
        $response->assertJsonPath('role.0', null);
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('id', $this->user->id)
                ->has('id')
                ->hasAll(['id', 'name'])
                ->hasAny('data', 'name')
                ->missing('password')
                ->etc()
        );
    }

    public function test_only_admin_can_fetch()
    {
        $response = $this->actingAs($this->user)->getJson('/api/user/' . $this->user2->id);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        $response = $this->actingAs($this->user)->getJson('/api/user');
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
    }

    public function test_update_user_info()
    {
        $response = $this->actingAs($this->admin)->putJson('/api/user/' . $this->user2->id, [
            'name' => 'updated name',
            'mobile' => '999'
        ]);
        $response->assertOk();
        $response->assertJson($this->user2->fresh()->toArray());
    }

    public function test_update_user_info_mobile_is_unique()
    {
        $response = $this->actingAs($this->admin)->putJson('/api/user/' . $this->user2->id, [
            'name' => 'updated name',
            'mobile' => $this->user->mobile
        ]);
        $response->assertUnprocessable();

        $response = $this->actingAs($this->admin)->putJson('/api/user/' . $this->user2->id, [
            'name' => 'updated name',
            'mobile' => $this->user2->mobile
        ]);
        $response->assertOk();
    }
}
