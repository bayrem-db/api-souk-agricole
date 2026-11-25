# api-souk-agricole

API REST d'un souk agricole : des agriculteurs publient des annonces de récolte, classées par produit et reliées à plusieurs régions. Le projet est écrit en Laravel 13 avec PHP 8.3+, une base SQLite et des jetons Sanctum pour les écritures. Il n'a pas de pages web : seule l'API JSON est exposée. La correction se fait depuis un clone propre au tag `v1.0`, en suivant uniquement ce README. Auteur : Bayrem, groupe MDW32.

## Description

Une annonce décrit une récolte avec son titre, sa description, son prix, sa quantité en kilogrammes, sa date de récolte et sa disponibilité. Chaque annonce appartient à un agriculteur (`User`), référence un produit (`Produit` : olives, dattes, amandes) et peut concerner plusieurs régions (`Region` : Sidi Bouzid, Kairouan, Tunis, Sfax, Gafsa). Les produits et les régions sont en lecture seule dans l'API. Les annonces sont paginées, filtrables par produit et protégées en écriture : seul leur auteur peut les modifier ou les supprimer.

## Les quatre rôles

| Rôle | Modèle | Relations | Exposé dans l'API |
|---|---|---|---|
| Propriétaire | `User` (Agriculteur) | `hasMany(Annonce)` | `annonces()` ; `UserResource` (id et nom seulement) |
| Ressource principale | `Annonce` | `belongsTo(User)`, `belongsTo(Produit)`, `belongsToMany(Region)` | lecture publique, écritures protégées par jeton et Policy |
| Ressource parente | `Produit` | `hasMany(Annonce)` | `/api/produits` et `/api/produits/{id}`, lecture seule, `annonces_count` |
| Étiquette | `Region` | `belongsToMany(Annonce)` via `annonce_region` (index unique composé et timestamps) | incluse dans chaque annonce avec `whenLoaded` |

La table `annonces` contient six colonnes métier : `titre`, `description`, `prix` (décimal, `min:0`), `quantite_kg` (entier, `min:1`), `date_recolte` (date) et `disponible` (booléen). Les deux dernières sont déclarées dans `casts()`, avec `prix` en `decimal:2`. Les clés étrangères utilisent `foreignId()->constrained()`.

## Installation

Le dépôt est `https://github.com/bayrem-db/api-souk-agricole.git`. Le projet n'a pas de pages ni de Vite : `php artisan serve` suffit, `npm` et `composer run dev` ne sont pas nécessaires. Si Artisan demande de créer `database/database.sqlite`, répondez `yes`.

### Bash

```bash
git clone https://github.com/bayrem-db/api-souk-agricole.git
cd api-souk-agricole
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

### PowerShell

```powershell
git clone https://github.com/bayrem-db/api-souk-agricole.git
cd api-souk-agricole
composer install
copy .env.example .env
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

Ouvrir ensuite http://localhost:8000 ou appeler directement une route `/api`. Le seeder crée 2 utilisateurs, 3 produits, 5 régions et 20 annonces ; chaque produit reçoit au moins une annonce. Suite verte : 10 tests / 45 assertions.

## Comptes de test

Créés par `php artisan migrate:fresh --seed`, avec le mot de passe `password` pour les deux comptes :

- `agriculteur1@example.com` — Agriculteur Un
- `agriculteur2@example.com` — Agriculteur Deux

Le premier compte possède les annonces créées par le seeder ; connectez le second pour vérifier qu'il reçoit 403 sur une modification. Le mot de passe est haché par la factory ; aucun mot de passe en clair n'est stocké dans la base. Les jetons s'obtiennent avec `POST /api/login` et aucun jeton réel n'est conservé dans le dépôt.

## Endpoints

| # | Méthode | URI | Accès | Remarque |
|---|---|---|---|---|
| 1 | POST | `/api/login` | public | renvoie un jeton Sanctum ; 422 si identifiants invalides |
| 2 | POST | `/api/logout` | jeton | révoque le jeton courant, 204 ; 401 sans jeton |
| 3 | GET | `/api/annonces` | public | paginé (10 par page), filtre `?produit=<slug>` exact |
| 4 | GET | `/api/annonces/{id}` | public | 404 JSON si inconnu |
| 5 | POST | `/api/annonces` | jeton | 201 ; le propriétaire est l'utilisateur connecté ; `regions` synchronisées |
| 6 | PUT/PATCH | `/api/annonces/{id}` | jeton + Policy | 401, 403, 404, 422 |
| 7 | DELETE | `/api/annonces/{id}` | jeton + Policy | 204, 401, 403, 404 |
| 8 | GET | `/api/produits` | public | avec `annonces_count` |
| 9 | GET | `/api/produits/{id}` | public | 404 JSON si inconnu |
| + | GET | `/api/user` | jeton | utilisateur courant, ajouté par `install:api` |

Les listes ont une clé `data` ; la liste paginée ajoute `links` et `meta`. Chaque annonce charge `user`, `produit` et `regions`, avec des dates ISO 8601 ; ni e-mail ni mot de passe de l'auteur ne sont exposés. Le filtre `?produit=olives` ne renvoie que les annonces dont le produit a ce slug exact ; un slug inconnu est refusé avec un message en français (422). `Model::preventLazyLoading(! app()->isProduction())` est activé dans `AppServiceProvider::boot()`.

## Authentification par jeton

Le groupe de routes `api` n'utilise ni session ni cookie. Chaque requête protégée présente l'en-tête `Authorization: Bearer <token>`. Le jeton brut n'est renvoyé qu'une fois par `/api/login` ; seule son empreinte SHA-256 est stockée dans `personal_access_tokens`. Révoquez-le avec `/api/logout` quand vous avez terminé. Le jeton n'expire pas par défaut.

```json
{"token":"VOTRE_JETON","token_type":"Bearer"}
```

`VOTRE_JETON` est un placeholder : aucun jeton réel n'apparaît dans ce README ni dans la collection Postman.

## Codes d'erreur

- `401` : jeton absent, invalide ou révoqué (`auth:sanctum`).
- `403` : la Policy refuse la modification ou la suppression (annonce d'un autre agriculteur).
- `404` : annonce ou produit inconnu, réponse JSON avec une clé `message`.
- `422` : validation (identifiants invalides, `prix` négatif, `quantite_kg` < 1, slug inconnu, régions absentes), messages en français.

L'ordre des contrôles est 401 (jeton), puis 403 (Policy dans `authorize()` de la Form Request, ou `Gate::authorize` en première ligne de `destroy`), puis 422 (validation des données). Ainsi un utilisateur non autorisé reçoit toujours 403, jamais 422.

## Exemple curl.exe

```bash
curl.exe -X POST -H "Accept: application/json" -H "Authorization: Bearer VOTRE_JETON" -H "Content-Type: application/json" -d "{\"produit_id\":1,\"titre\":\"Olives bio\",\"description\":\"Recolte locale\",\"prix\":12.5,\"quantite_kg\":20,\"date_recolte\":\"2026-10-01\",\"disponible\":true,\"regions\":[1,2]}" http://localhost:8000/api/annonces
```

La réponse 204 de `/api/logout` n'a pas de corps ; la commande n'affiche donc rien quand le jeton est valide.

## Collection Postman

La collection `docs/postman/api-souk-agricole.postman_collection.json` est au format Collection v2.1. Elle utilise les variables `{{base_url}}` (`http://localhost:8000`) et `{{token}}` (vide dans le fichier exporté). La requête `Login` enregistre le jeton dans la variable de collection ; les requêtes protégées héritent de l'authentification Bearer de la collection. La requête `Creer une annonce` enregistre l'identifiant dans `{{annonce_id}}` pour les requêtes `Modifier` et `Supprimer`. Aucun jeton n'est présent dans le fichier.

## Choix d'architecture

### Pourquoi la ressource principale appartient-elle à l'utilisateur, et que protège la Policy ?

L'annonce est ce que l'agriculteur crée et dont il est responsable : c'est donc elle qui porte `user_id`, pas le produit ni la région, qui sont des référentiels partagés. La Policy protège un risque concret du domaine : un autre agriculteur ne doit pas pouvoir modifier le prix d'une annonce, réduire la quantité annoncée, changer la date de récolte ni supprimer l'annonce d'un concurrent. `update` et `delete` comparent l'identifiant du propriétaire à l'utilisateur authentifié. La Form Request de modification appelle la Policy dans `authorize()`, avant la validation : un compte étranger reçoit 403 et non 422. `destroy`, qui n'a pas de Form Request, commence par `Gate::authorize('delete', $annonce)`.

### À quel besoin réel d'un utilisateur de l'API répond le filtre ?

Un acheteur qui cherche « quelles annonces d'olives sont disponibles ? » ne doit pas parcourir les dix pages d'annonces. Le filtre `?produit=olives` répond directement à ce besoin ; il compare le slug exact du produit référencé,通过 `whereHas('produit', ...)`. La même logique servirait à un tableau de bord d'un producteur qui veut suivre ses offres pour une culture donnée. Un slug inconnu est refusé avec 422 plutôt que de renvoyer silencieusement une liste vide : le client voit ainsi qu'il a fait une faute de frappe.

### Quelle requête produirait un problème N+1 sans with(), et comment l'avez-vous évité ?

`GET /api/annonces` affiche pour chaque annonce son auteur, son produit et ses régions. Sans `with(['user', 'produit', 'regions'])`, une page de 10 annonces déclencherait 1 requête pour la liste, puis 10 requêtes pour les auteurs, 10 pour les produits et 10 pour les régions : 31 requêtes au lieu de 4. Le contrôleur charge ces trois relations en une seule passe, et `AnnonceResource` ne les lit qu'avec `whenLoaded`, ce qui évite de déclencher un chargement paresseux depuis la Resource. `Model::preventLazyLoading(! app()->isProduction())` transforme toute relation oubliée en erreur visible en développement. Côté produits, `GET /api/produits` utilise `withCount('annonces')` et `ProduitResource` lit ce compte avec `whenCounted`, pour la même raison.
