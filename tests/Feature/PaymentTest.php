<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    public function test_create_new_payment()
    {

        $response = $this->actingAs($this->admin)->postJson('api/payment', ['name' => 'payment', 'type' => 3]);
        $response->assertCreated();
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_apply_payment()
    {
        $payment = Payment::factory()->create();
        $account_name = $this->user->name . ' account name';
        $number = fake()->randomNumber(5);
        $data = [
            'user_id' => $this->user->id,
            'payment_id' => $payment->id,
            'account_name' => $account_name,
            'number' => (string)$number
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user/payment', $data);
        $response->assertOk();
        $this->assertDatabaseCount('user_payment', 1);
        $this->assertDatabaseHas('user_payment', $data);
        $data['created_at'] = $response->json()['payments'][0]['pivot']['created_at'];
        $HTTP_RAW_POST_DATA['updated_at'] = $response->json()['payments'][0]['pivot']['updated_at'];
        $response->assertJson(
            fn (AssertableJson $json) => $json->hasAll('id', 'name', 'mobile', 'created_at', 'updated_at', 'pictures', 'roles', 'payments')
                ->has(
                    'payments.0',
                    fn (AssertableJson $json) => $json->where('id', $payment->id)
                        ->where('pivot', $data)
                        ->etc()
                )

        );
    }
}
