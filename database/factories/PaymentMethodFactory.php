<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();
        return [
            'name' => $name,
            'slug' => $this->faker->slug(),
            'class' => $name,
            'configuration' => ['field1' => 'value1', 'field2' => 'value2'],
            'required_fields' => ['field1', 'field2'],
            'active' => 1,
        ];
    }
}
