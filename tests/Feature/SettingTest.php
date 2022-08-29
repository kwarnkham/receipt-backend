<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SettingTest extends TestCase
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

    public function test_set_setting_for_user_table_color()
    {
        $response = $this->actingAs($this->user)->postJson('api/setting/' . $this->user->id, [
            'table_color' => '#719189'
        ]);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        $response = $this->actingAs($this->admin)->postJson('api/setting/' . $this->user->id, [
            'table_color' => '#719189'
        ]);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->where('table_color', '#719189')->etc());
        $this->assertDatabaseCount('settings', 1);

        $response = $this->actingAs($this->admin)->postJson('api/setting/' . $this->user->id, [
            'table_color' => '#719180'
        ]);
        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->where('table_color', '#719180')->etc());
        $this->assertDatabaseCount('settings', 1);
    }
}
