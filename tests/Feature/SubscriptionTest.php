<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscriptionTest extends TestCase
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

    public function test_user_subscribes()
    {
        $data = [
            'user_id' => $this->user->id,
            'day' => 30,
            'price' => 7000,
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $response->assertJson($data);
    }

    public function test_only_admin_can_do_user_subscribes()
    {
        $data = [
            'user_id' => $this->user->id,
            'day' => 30,
            'price' => 7000,
        ];
        $response = $this->actingAs($this->user)->postJson('api/subscription', $data);

        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
    }

    public function test_user_subscribes_accumulated()
    {
        $data = [
            'user_id' => $this->user->id,
            'day' => 30,
            'price' => 7000,
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $response->assertJson($data);

        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $data['duration'] = $data['day'] * 2;
        $response->assertCreated();
        $response->assertJson($data);

        $this->assertEquals($this->user->latestSubscription->remainingDuration, $data['duration']);

        $this->travel(60)->days();

        $data = [
            'user_id' => $this->user->id,
            'day' => 30,
            'price' => 7000
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);
        $response->assertJson($data);

        $this->travel(25)->days();

        $data = [
            'user_id' => $this->user->id,
            'day' => 30,
            'price' => 7000
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);
        $data['duration'] = $data['day'] + 5;
        $response->assertJson($data);
        $this->user = $this->user->fresh();

        $this->assertEquals($this->user->latestSubscription->remainingDuration, $data['duration']);
    }
}
