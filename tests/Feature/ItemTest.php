<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ItemTest extends TestCase
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

    public function test_get_known_items()
    {
        $count = 10;
        Item::factory($count)->create([
            'user_id' => $this->user->id
        ]);
        Item::factory($count)->create([
            'user_id' => $this->user2->id
        ]);
        $response = $this->actingAs($this->user)->getJson('api/item/known');
        $response->assertJson(fn (AssertableJson $json) => $json->has($count)->first(fn ($json) => $json->hasAll('name', 'price')));
        $response->assertStatus(200);
    }
}
