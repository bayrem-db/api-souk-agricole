<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Sidi Bouzid', 'Kairouan', 'Tunis', 'Sfax', 'Gafsa'] as $name) {
            Region::factory()->create([
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', $name)),
            ]);
        }
    }
}
