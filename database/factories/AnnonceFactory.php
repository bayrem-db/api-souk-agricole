<?php

namespace Database\Factories;

use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Annonce>
 */
class AnnonceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'produit_id' => Produit::factory(),
            'titre' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'prix' => fake()->randomFloat(2, 1, 100),
            'quantite_kg' => fake()->numberBetween(1, 500),
            'date_recolte' => fake()->date(),
            'disponible' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the announcement is still available.
     */
    public function disponible(): static
    {
        return $this->state(fn (array $attributes) => [
            'disponible' => true,
        ]);
    }
}
