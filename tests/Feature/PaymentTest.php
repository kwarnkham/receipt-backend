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

    public function test_fetch_payments()
    {
        $payments = Payment::factory(5)->create();
        $response = $this->getJson('api/payment');
        $response->assertOk();
        $response->assertJson($payments->toArray());
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
        $data['updated_at'] = $response->json()['payments'][0]['pivot']['updated_at'];
        $response->assertJson(
            fn (AssertableJson $json) => $json->hasAll('id', 'name', 'mobile', 'created_at', 'updated_at', 'pictures', 'roles', 'payments', 'latest_subscription')
                ->has(
                    'payments.0',
                    fn (AssertableJson $json) => $json->where('id', $payment->id)
                        ->etc()
                )

        );
    }

    public function test_update_payment_info_of_a_user()
    {
        $payment = Payment::factory()->create();
        $account_name = $this->user->name . ' account name';
        $number = (string) fake()->randomNumber(5);
        $data = [
            'user_id' => $this->user->id,
            'payment_id' => $payment->id,
            'account_name' => $account_name,
            'number' => $number
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user/payment', $data);
        $response->assertOk();

        $data = ['account_name' => 'updated name', 'number' => 'updated number'];
        $response = $this->actingAs($this->admin)->putJson('api/user/' . $this->user->id . '/payment/' . $payment->id . '/number/' . $number, $data);
        $response->assertOk();

        $response->assertJson(
            fn (AssertableJson $json) => $json->hasAll('id', 'name', 'type', 'created_at', 'updated_at', 'pivot')
                ->has(
                    'pivot',
                    fn (AssertableJson $json) => $json->where('user_id', $this->user->id)
                        ->where('payment_id', $payment->id)
                        ->where('account_name', $data['account_name'])
                        ->where('number', $data['number'])
                        ->etc()
                )

        );
    }

    public function test_can_only_update_info_of_assigned_payment_to_a_user()
    {
        $payment = Payment::factory()->create();
        $payment2 = Payment::factory()->create();
        $account_name = $this->user->name . ' account name';
        $number = (string) fake()->randomNumber(5);
        $data = [
            'user_id' => $this->user->id,
            'payment_id' => $payment->id,
            'account_name' => $account_name,
            'number' => $number
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user/payment', $data);
        $response->assertOk();

        $data = ['account_name' => 'updated name', 'number' => 'updated number'];
        $response = $this->actingAs($this->admin)->putJson('api/user/' . $this->user->id . '/payment/' . $payment2->id . '/number/' . $number, $data);
        $response->assertNotFound();
        $response = $this->actingAs($this->admin)->putJson('api/user/' . $this->user->id . '/payment/' . $payment->id . '/number/' . $number, $data);
        $response->assertOk();
    }

    public function test_apply_payment_exclude_account_name()
    {
        $payment = Payment::factory()->create();
        $number = fake()->randomNumber(5);
        $data = [
            'user_id' => $this->user->id,
            'payment_id' => $payment->id,
            'number' => (string)$number,
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user/payment', $data);
        $response->assertOk();
        $this->assertDatabaseCount('user_payment', 1);
        $this->assertDatabaseHas('user_payment', $data);
        $data['created_at'] = $response->json()['payments'][0]['pivot']['created_at'];
        $data['updated_at'] = $response->json()['payments'][0]['pivot']['updated_at'];
        $data['account_name'] = null;
        $response->assertJson(
            fn (AssertableJson $json) => $json->hasAll('id', 'name', 'mobile', 'created_at', 'updated_at', 'pictures', 'roles', 'payments', 'latest_subscription')
                ->has(
                    'payments.0',
                    fn (AssertableJson $json) => $json->where('id', $payment->id)
                        ->etc()
                )

        );
    }

    public function test_delete_payment_info_of_a_user()
    {
        $payment = Payment::factory()->create();
        $account_name = $this->user->name . ' account name';
        $number = (string) fake()->randomNumber(5);
        $data = [
            'user_id' => $this->user->id,
            'payment_id' => $payment->id,
            'account_name' => $account_name,
            'number' => $number
        ];
        $response = $this->actingAs($this->admin)->postJson('api/user/payment', $data);
        $response->assertOk();

        $data = ['account_name' => 'updated name', 'number' => 'updated number'];
        $response = $this->actingAs($this->admin)->deleteJson('api/user/' . $this->user->id . '/payment/' . $payment->id . '/number/' . $number);
        $response->assertOk();
        $this->assertDatabaseCount('user_payment', 0);
    }
}
