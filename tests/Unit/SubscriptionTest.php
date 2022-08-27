<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_remaining_duration()
    {
        $duration = 30;
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'duration' => $duration
        ]);

        $this->assertEquals($subscription->remainingDuration(), $duration);

        for ($i = 1; $i <= $duration; $i++) {
            $this->travel($i)->days();
            $this->assertEquals($subscription->remainingDuration(), $duration - $i);
            $this->travelBack();
        }
        $this->travel(31)->days();

        $this->assertLessThan(0, $subscription->remainingDuration());

        $this->assertTrue($subscription->expired);
        $this->assertFalse($subscription->active);
    }
}
