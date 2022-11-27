<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Phone;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PhoneTest extends TestCase
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

    public function test_add_a_phone()
    {
        $data = [
            'user_id' => $this->user->id,
            'number' => '0912312312'
        ];
        $this->actingAs($this->user)->postJson(route('phones.store'), $data)->assertCreated()->assertJson($data);

        $this->assertDatabaseCount('phones', 1);
    }

    public function test_can_only_add_max_of_two_phones()
    {
        $data = [
            'user_id' => $this->user->id,
            'number' => '0912312312'
        ];
        $this->actingAs($this->user)->postJson(route('phones.store'), $data)->assertCreated()->assertJson($data);

        $this->assertDatabaseCount('phones', 1);

        $data = [
            'user_id' => $this->user->id,
            'number' => '0912312312'
        ];
        $this->actingAs($this->user)->postJson(route('phones.store'), $data)->assertCreated()->assertJson($data);

        $this->assertDatabaseCount('phones', 2);

        $data = [
            'user_id' => $this->user->id,
            'number' => '0912312312'
        ];
        $this->actingAs($this->user)->postJson(route('phones.store'), $data)->assertStatus(ResponseStatus::BAD_REQUEST->value);

        $this->assertDatabaseCount('phones', 2);
    }

    public function test_delete_a_phone()
    {
        $phone = Phone::factory()->create(['user_id' => $this->user->id]);
        $this->assertDatabaseCount('phones', 1);

        $this->actingAs($this->user)->deleteJson(route('phones.destroy', ['phone' => $phone->id]))->assertOk();
        $this->assertDatabaseCount('phones', 0);
    }

    public function test_update_a_phone()
    {
        $data = Phone::factory()->make()->toArray();
        $phone = Phone::factory()->create(['user_id' => $this->user->id]);
        $this->assertDatabaseCount('phones', 1);

        $this->actingAs($this->user)->putJson(route('phones.update', ['phone' => $phone->id]), $data)->assertOk()->assertJson($data);
    }
}
