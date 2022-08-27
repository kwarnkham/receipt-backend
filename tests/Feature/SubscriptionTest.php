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
            'duration' => 30
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $response->assertJson($data);
    }

    public function test_only_admin_can_do_user_subscribes()
    {
        $data = [
            'user_id' => $this->user->id,
            'duration' => 30
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
            'duration' => 30
        ];
        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $response->assertJson($data);

        $response = $this->actingAs($this->admin)->postJson('api/subscription', $data);

        $response->assertCreated();
        $data['duration'] *= 2;
        $response->assertJson($data);

        $this->assertEquals($this->user->latestSubscription->remainingDuration(), $data['duration']);
    }
}
