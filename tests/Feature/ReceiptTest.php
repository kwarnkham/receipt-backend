<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => bcrypt('123123'),
        ]);
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
}
