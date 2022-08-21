<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReceiptTest extends TestCase
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

    public function test_create_receipt()
    {

        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'discount' => fake()->numberBetween(100, 200),
            'deposit' => 1000,
            'items' => [
                [
                    'name' => fake()->unique()->name(),
                    'price' => 1000,
                    'quantity' => 1,
                ],
                [
                    'name' => fake()->unique()->name(),
                    'price' => 2000,
                    'quantity' => 1,
                ]
            ]
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseCount('receipt_item', 2);
        $this->assertDatabaseCount('items', 2);
    }

    public function test_create_receipt_without_discount_and_deposit()
    {
        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'items' => [
                [
                    'name' => fake()->unique()->name(),
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ]
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseCount('receipt_item', 1);
        $this->assertDatabaseCount('items', 1);
    }

    public function test_create_receipt_from_existing_items()
    {
        $items = Item::factory(2)->create([
            'user_id' => $this->user->id
        ]);
        $items = $items->map(function ($value) {
            $value->quantity = 1;
            return $value;
        });
        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'items' => $items
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseCount('receipt_item', 2);
        $this->assertDatabaseCount('items', 2);

        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'items' => $items
        ]);
        $this->assertDatabaseCount('receipts', 2);
        $this->assertDatabaseCount('receipt_item', 4);
        $this->assertDatabaseCount('items', 2);
    }

    public function test_find_a_receipt()
    {
        $receipt = Receipt::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->getJson('/api/receipt/' . $receipt->id);
        $response->assertOk();
        $response->assertJson($receipt->toArray());
    }

    public function test_can_find_only_owned_receipt()
    {
        $receipt = Receipt::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user2)->getJson('/api/receipt/' . $receipt->id);
        $response->assertNotFound();
    }

    public function test_create_receipt_update_existing_item_price()
    {
        $items = Item::factory(2)->create([
            'user_id' => $this->user->id,
            'price' => 500
        ]);
        $items = $items->map(function ($value) {
            $value->quantity = 1;
            return $value;
        });
        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'items' => $items
        ]);

        $oldValues = $response->json()['items'];


        $response->assertCreated();
        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseCount('receipt_item', 2);
        $this->assertDatabaseCount('items', 2);
        $items = $items->map(function ($val) {
            $val->price = 1000;
            return $val;
        });
        $response = $this->actingAs($this->user)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'items' => $items
        ]);
        $this->assertDatabaseCount('receipts', 2);
        $this->assertDatabaseCount('receipt_item', 4);
        $this->assertDatabaseCount('items', 2);

        $values = $response->json()['items'];
        foreach ($values as $value) {
            $this->assertEquals($value['pivot']['price'], $items->first(fn ($val) => $val->id == $value['id'])->price);
            $this->assertEquals($value['pivot']['price'], 1000);
        }

        foreach ($oldValues as $value) {
            $this->assertNotEquals($value['pivot']['price'], $items->first(fn ($val) => $val->id == $value['id'])->price);
            $this->assertEquals($value['pivot']['price'], 500);
        }
    }

    public function test_retrieve_receipts()
    {
        $receipt = Receipt::factory(10)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/receipt');
        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', $receipt->count())
                ->has('data.0', fn ($json) =>
                $json->where('id', $receipt[0]->id)
                    ->where('customer_name', $receipt[0]->customer_name)->etc())
        );
    }

    public function test_retrieve_receipts_only_owned()
    {
        $receipt = Receipt::factory(10)->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user2)->getJson('/api/receipt');
        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', 0)

        );

        $response = $this->actingAs($this->user)->getJson('/api/receipt');
        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll('current_page', 'data', 'first_page_url', 'from', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'last_page', 'last_page_url', 'links', 'total')
                ->has('data', $receipt->count())

        );
    }

    public function test_admin_cannot_create_receipt()
    {
        $response = $this->actingAs($this->admin)->postJson('api/receipt', [
            'date' => now(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'discount' => fake()->numberBetween(100, 200),
            'deposit' => 1000,
            'items' => [
                [
                    'name' => fake()->unique()->name(),
                    'price' => 1000,
                    'quantity' => 1,
                ],
                [
                    'name' => fake()->unique()->name(),
                    'price' => 2000,
                    'quantity' => 1,
                ]
            ]
        ]);

        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
    }
}
