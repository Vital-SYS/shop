<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => rand(1, 4),
            'brand_id' => rand(1, 4),
            'name' => $this->faker->name,
            'content' => $this->faker->realText(rand(400, 500)),
            'slug' => Str::slug($this->faker->name),
            'price' => rand(1000, 2000),
        ];
    }
}
