<?php

namespace Database\Seeders;

use App\Models\Produit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProduitSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Olives', 'Dattes', 'Amandes'] as $name) {
            Produit::factory()->create([
                'name' => $name,
                'slug' => strtolower($name),
            ]);
        }
    }
}
