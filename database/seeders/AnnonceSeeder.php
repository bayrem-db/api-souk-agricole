<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\Produit;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnonceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $produits = Produit::all();
        $regions = Region::all();

        $annonces = Annonce::factory()
            ->count(20)
            ->disponible()
            ->make([
                'user_id' => fn () => $users->random()->id,
                'produit_id' => fn () => $produits->random()->id,
            ]);

        foreach ($annonces as $index => $annonce) {
            $annonce->user()->associate($users[$index % $users->count()]);
            $annonce->produit()->associate($produits[$index % $produits->count()]);
            $annonce->save();
            $annonce->regions()->attach($regions->random(2));
        }
    }
}
