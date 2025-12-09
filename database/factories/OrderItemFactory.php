<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => null,
            'product_id' => function () {
                return Product::factory()->create()->id;
            },
            'qty' => 1,
            'price' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                return $product ? $product->price : $this->faker->randomFloat(2, 5, 200);
            },
        ];
    }
}
