# RestaurantAI-Brigade API (Laravel 11 + Sanctum)

API REST pour une application de recommandation de plats : authentification Sanctum, profil alimentaire, gestion admin (catégories / plats / ingrédients) et recommandations asynchrones (Queue & Jobs).

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Pour exécuter les Jobs (asynchrone) :

```bash
php artisan queue:work
```

## LLM (Grok / LLaMA)

Par défaut, le score est calculé par règles (rapide) et le Job peut **optionnellement** utiliser un LLM pour générer une explication plus naturelle.

Configurer dans `.env` :

- `LLM_ENABLED=true`
- `LLM_BASE_URL` (OpenAI-compatible, ex: `http://127.0.0.1:11434/v1` pour Ollama)
- `LLM_API_KEY` (vide pour Ollama, requis pour un provider cloud)
- `LLM_MODEL` (ex: `llama3.1`)
- `LLM_SCORING_MODE=rules|ai`

## Demo UI + Docs

- UI de démonstration : `GET /demo`
- Swagger UI : `GET /docs` (spec: `public/openapi.yaml`)
- Postman: `docs/postman/restaurantai_brigade.postman_collection.json` + `docs/postman/restaurantai_brigade.postman_environment.json`

## Comptes seed (demo)

- Admin: `admin@demo.test` / `password123`
- User: `user@demo.test` / `password123`

## Endpoints (principaux)

Auth:
- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

Profile:
- `GET /api/profile`
- `PUT /api/profile`

Categories:
- `GET /api/categories`
- `POST /api/categories` (admin)
- `GET /api/categories/{id}`
- `PUT /api/categories/{id}` (admin)
- `DELETE /api/categories/{id}` (admin)
- `GET /api/categories/{id}/plates`

Plates:
- `GET /api/plates`
- `POST /api/plates` (admin)
- `GET /api/plates/{id}`
- `PUT /api/plates/{id}` (admin)
- `DELETE /api/plates/{id}` (admin)

Ingredients (admin only):
- `GET /api/ingredients`
- `POST /api/ingredients`
- `PUT /api/ingredients/{id}`
- `DELETE /api/ingredients/{id}`

Recommendations:
- `POST /api/recommendations/analyze/{plate_id}`
- `GET /api/recommendations`
- `GET /api/recommendations/{plate_id}`

## Tests

Par défaut, `phpunit.xml` utilise SQLite en mémoire (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).

Si vos tests échouent avec “could not find driver”, activez `pdo_sqlite` et `sqlite3` dans votre `php.ini`, puis exécutez :

```bash
php artisan test
```
