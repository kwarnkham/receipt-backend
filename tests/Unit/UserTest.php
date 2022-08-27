<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_revokeAccessTokensOfExpiredSubscriptions()
    {
        $user = User::factory()->create();
        $user->createToken('test');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        Subscription::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseCount('subscriptions', 1);

        $this->travel(30)->days();
        $this->assertTrue($user->latestSubscription->active);
        $this->assertFalse($user->latestSubscription->expired);

        $this->travel(1)->days();
        $this->assertFalse($user->latestSubscription->active);
        $this->assertTrue($user->latestSubscription->expired);
        User::revokeAccessTokensOfExpiredSubscriptions();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
