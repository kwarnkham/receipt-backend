<?php

namespace Tests\Unit;

use App\Enums\ResponseStatus;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

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

    public function test_remaining_duration()
    {
        $duration = fake()->randomNumber(3);
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'duration' => $duration,
            'day' => $duration
        ]);

        $this->assertEquals($subscription->remainingDuration(), $duration);

        for ($i = 1; $i <= $duration; $i++) {
            $this->travel($i)->days();
            $this->assertEquals($subscription->remainingDuration(), $duration - $i);
            $this->travelBack();
        }
        $this->travel($duration + 1)->days();

        $this->assertLessThan(0, $subscription->remainingDuration());

        $this->assertTrue($subscription->expired);
        $this->assertFalse($subscription->active);
    }

    public function test_active_status_of_a_subscription()
    {
        $duration = fake()->randomNumber(2);
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'duration' => $duration,
            'day' => $duration,

        ]);
        $this->assertEquals($subscription->remainingDuration(), $duration);
        $this->travel($duration)->days();
        $this->assertFalse($subscription->expired);
        $this->assertTrue($subscription->active);
        $this->travel($duration + 1)->days();
        $this->assertFalse($subscription->active);
        $this->assertTrue($subscription->expired);
    }

    public function test_add_days_to_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'day' => 30,
            'duration' => 30
        ]);
        $days = fake()->randomNumber();
        $response = $this->actingAs($this->user)->postJson('api/subscription/' . $subscription->id . '/add', [
            'days' => $days
        ]);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        $response = $this->actingAs($this->admin)->postJson('api/subscription/' . $subscription->id . '/add', [
            'days' => $days
        ]);
        $response->assertOk();

        $data = $subscription->toArray();
        $data['duration'] += $days;
        $response->assertJson($data);
        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('duration')
            ->where('duration', $data['duration'])
            ->etc());
    }
}
