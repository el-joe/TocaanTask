<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'status' => 'pending',
        ];
    }

    public function withItems(int $count = 2): self
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            OrderItem::factory()->count($count)->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
