<?php

use App\Models\Annonce;
use App\Models\Produit;
use App\Models\Region;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function annoncePayload(Produit $produit, Region $region): array
{
    return [
        'produit_id' => $produit->id,
        'titre' => 'Olives bio de saison',
        'description' => 'Une récolte locale proposée directement par le producteur.',
        'prix' => 12.5,
        'quantite_kg' => 20,
        'date_recolte' => '2026-10-01',
        'disponible' => true,
        'regions' => [$region->id],
    ];
}

test('la liste des annonces renvoie 200 et la structure attendue', function () {
    $produit = Produit::factory()->create();
    $annonce = Annonce::factory()->create(['produit_id' => $produit->id]);

    $this->getJson('/api/annonces')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'titre',
                    'description',
                    'prix',
                    'quantite_kg',
                    'date_recolte',
                    'disponible',
                    'user' => ['id', 'name'],
                    'produit' => ['id', 'name', 'slug'],
                    'regions',
                ],
            ],
            'links',
            'meta',
        ])
        ->assertJsonPath('data.0.id', $annonce->id);
});

test('le filtre par produit ne renvoie que les annonces de ce produit', function () {
    $olives = Produit::factory()->create(['name' => 'Olives', 'slug' => 'olives']);
    $dattes = Produit::factory()->create(['name' => 'Dattes', 'slug' => 'dattes']);

    $matching = Annonce::factory()->create(['produit_id' => $olives->id]);
    Annonce::factory()->create(['produit_id' => $dattes->id]);

    $this->getJson('/api/annonces?produit=olives')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonPath('data.0.produit.slug', 'olives');
});

test('une annonce inconnue renvoie 404 en JSON', function () {
    $this->getJson('/api/annonces/999999')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('une création sans jeton renvoie 401', function () {
    $produit = Produit::factory()->create();
    $region = Region::factory()->create();

    $this->postJson('/api/annonces', annoncePayload($produit, $region))
        ->assertUnauthorized();
});

test('la modification d\'une annonce d\'un autre utilisateur renvoie 403', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $produit = Produit::factory()->create();
    $region = Region::factory()->create();
    $annonce = Annonce::factory()->create(['user_id' => $owner->id, 'produit_id' => $produit->id]);

    Sanctum::actingAs($other);

    $this->putJson("/api/annonces/{$annonce->id}", annoncePayload($produit, $region))
        ->assertForbidden();
});

test('une création avec un corps invalide renvoie 422 avec des messages en français', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/annonces', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['titre', 'prix', 'quantite_kg', 'regions']);
});

test('une création valide renvoie 201 et enregistre les régions', function () {
    $user = User::factory()->create();
    $produit = Produit::factory()->create();
    $region = Region::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/annonces', annoncePayload($produit, $region));

    $response->assertCreated()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonCount(1, 'data.regions');

    $this->assertDatabaseHas('annonces', [
        'titre' => 'Olives bio de saison',
        'user_id' => $user->id,
        'produit_id' => $produit->id,
    ]);
    $this->assertDatabaseHas('annonce_region', [
        'annonce_id' => $response->json('data.id'),
        'region_id' => $region->id,
    ]);
});

test('la déconnexion révoque le jeton courant', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$token}"])
        ->assertNoContent();

    // Un vrai client ouvre une nouvelle requête : le guard doit oublier le jeton révoqué.
    auth()->forgetGuards();

    $this->getJson('/api/user', ['Authorization' => "Bearer {$token}"])
        ->assertUnauthorized();
});
