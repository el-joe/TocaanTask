<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'payment_method_id' => null,
            'order_id' => null,
            'transaction_id' => $this->faker->uuid(),
            'status' => 'pending',
            'pay_details' => [],
            'callback_details' => [],
        ];
    }
}
