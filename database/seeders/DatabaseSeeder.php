<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Agriculteur Un',
            'email' => 'agriculteur1@example.com',
        ]);

        User::factory()->create([
            'name' => 'Agriculteur Deux',
            'email' => 'agriculteur2@example.com',
        ]);

        $this->call([
            ProduitSeeder::class,
            RegionSeeder::class,
            AnnonceSeeder::class,
        ]);
    }
}
