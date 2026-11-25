<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Sidi Bouzid', 'Kairouan', 'Tunis', 'Sfax', 'Gafsa']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
