# what2play - API

what2play is a REST API that recommends video games. Users keep a personal library, set their preferences (genres, game modes and a maximum price), and get a ranked list of games they have not completed yet.

It is a rewrite of [what2play-legacy](https://github.com/Gonzalo-fs/what2play-legacy), the first project I built with Laravel. The idea, the domain and the data come from there. Everything else was rebuilt from scratch with what I have learned since. To see what changed and why, read the [what2play-legacy README](https://github.com/Gonzalo-fs/what2play-legacy#readme).

Built with Laravel 13, PHP 8.3 and MySQL. This is an MVP, so it is API only and there is no front end in this repository. A front end built with Vue.js is planned for the future.

## Features

- Public catalog of games with filters by genre, mode and maximum price.
- Token authentication (Laravel Sanctum, Bearer tokens with expiration).
- Personal library: save games and mark them as completed.
- User preferences: favourite genres, favourite modes and a maximum price.
- Recommendations ranked by how many preferred genres and modes a game matches, then by score.
- Admin endpoints to create, update, delete (soft delete) and upload images for games.

## Stack

- PHP 8.3, Laravel 13, MySQL 8
- Laravel Sanctum for API tokens
- Pest for tests, Laravel Pint for code style, Larastan (PHPStan) for static analysis
- GitHub Actions running Pint, Larastan and Pest on pushes to `main` and on pull requests

## Getting started

Requirements: PHP 8.3 with the usual Laravel extensions, Composer and MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database called `what2play` (or change `DB_*` in `.env`), then:

```bash
php artisan migrate --seed
php artisan storage:link   # only needed to serve uploaded game images
php artisan serve
```

The API is now available at `http://localhost:8000/api/v1`.

### Demo data

The seeders load a real catalog: 22 games with their developers, 13 genres and 3 game modes. The catalog seeders are idempotent, but the demo users are created with a factory, so use `php artisan migrate:fresh --seed` to reseed from scratch. Two demo users are created, both with the password `password`:

| User | Role | Notes |
| --- | --- | --- |
| `admin@example.com` | Admin | Can use the `/admin` endpoints. |
| `test@example.com` | Regular user | Has preferences and a small library, so recommendations are ready to try. |

## Authentication

Register or log in to get a token, then send it on every protected request:

```
Authorization: Bearer <token>
Accept: application/json
```

Tokens expire after `SANCTUM_TOKEN_EXPIRATION_MINUTES` (default 1440, one day). Register and login are throttled to 5 requests per minute.

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

```json
{
  "data": {
    "token": "1|...",
    "user": { "id": 2, "name": "...", "email": "test@example.com", "is_admin": false, "max_price": "30.00" }
  }
}
```

## Endpoints

All routes are prefixed with `/api/v1`. Game lists (`/games`, `/me/library`, `/recommendations`) are paginated (15 per page, `?page=N`) and wrapped in `data`, `links` and `meta`. `/genres` and `/modes` return the full list inside `data`.

### Public

| Method | Path | Description |
| --- | --- | --- |
| POST | `/auth/register` | Create an account. Body: `name`, `email`, `password`, `password_confirmation` (min. 8). Returns 201 with a token. |
| POST | `/auth/login` | Body: `email`, `password`. Returns a token, or 401 for wrong credentials. |
| GET | `/games` | Paginated catalog, best score first. Filters: `genre_ids[]`, `mode_ids[]`, `max_price`. |
| GET | `/games/{slug}` | A single game. |
| GET | `/genres` | All genres. |
| GET | `/modes` | All game modes. |

Filter semantics: within `genre_ids[]` or `mode_ids[]` a game matches if it has any of the given ids. When both filters are sent, it must match both (any genre and any mode). `max_price` also keeps games without a price.

```
GET /api/v1/games?genre_ids[]=1&genre_ids[]=4&max_price=20
```

### Authenticated

| Method | Path | Description |
| --- | --- | --- |
| POST | `/auth/logout` | Revokes the current token (204). |
| GET | `/me` | The current user. |
| GET | `/me/preferences` | Preferred genres, modes and maximum price. |
| PUT | `/me/preferences` | Replaces them. Body: `genre_ids` (array), `mode_ids` (array), `max_price` (nullable number). |
| GET | `/me/library` | The user's games, most recently updated first. Filters: `saved`, `completed` (booleans). |
| PUT | `/me/library/{slug}` | Adds a game or updates its flags. Body: `saved`, `completed` (booleans, both optional). A new entry defaults to `saved: true`, `completed: false`. |
| DELETE | `/me/library/{slug}` | Removes a game from the library (204). |
| GET | `/recommendations` | Ranked recommendations for the user (see below). |

### Admin

Require a user with `is_admin = true`. Other users get 403.

| Method | Path | Description |
| --- | --- | --- |
| POST | `/admin/games` | Creates a game (201). Required: `title`, `description`, `developer_id`. `genre_ids` and `mode_ids` must be sent, but they can be empty arrays. Optional: `slug` (generated from the title when omitted), `release_year`, `price`, `score` (0 to 10), `buy_url`, `video_url`, `difficulty`, `audience`, `graphics`, `requirements`, `requirements_specs`, `game_length_minutes`, `disclaimer`. |
| PATCH | `/admin/games/{slug}` | Partial update with the same fields. |
| DELETE | `/admin/games/{slug}` | Soft deletes the game (204). |
| POST | `/admin/games/{slug}/image` | Multipart upload, field `image` (jpg, png or webp, max 2 MB). Stored on the `public` disk. |

### Errors

| Status | Meaning |
| --- | --- |
| 401 | Missing, invalid or expired token, or wrong login credentials. |
| 403 | The user is not allowed to perform the action. |
| 404 | The resource does not exist (soft-deleted games included). |
| 422 | Validation failed. The body lists the errors per field. |
| 429 | Too many register or login attempts. |

API routes always answer in JSON, even without an `Accept` header.

## How recommendations work

`GameFilterService::filter()` builds the list from filters passed in as parameters. `RecommendationController` passes the user's preferences, and `GameController` passes the query string filters.

Recommendations (default mode):

1. Exclude games the user has already completed.
2. Exclude games above the user's `max_price` (games without a price are kept).
3. If the user has preferences, keep only games matching at least one preferred genre or mode and sort them by the number of matches.
4. Break ties by score (games without score last).

A user with no preferences gets the catalog ordered by score.

Catalog (`GET /games`, strict mode): when both filters are sent, a game must match any of the genres and any of the modes. A filter that is not sent is ignored. Results are ordered by score.

## Architecture

- Controllers hold the use case of each endpoint. There is no Actions layer and no repository layer.
- The only service, `GameFilterService`, holds the filtering and ranking logic shared by the catalog and the recommendations, so it does not live in the controllers. New services should only appear when the same logic is needed from more than one place.
- Query filters are reusable Eloquent scopes on `Game`.
- Validation lives in Form Requests, authorization in `GamePolicy`, and responses are shaped by API Resources.
- Many-to-many relations use pivot tables: `game_genre`, `game_mode`, `game_user` (with `saved` and `completed`), `user_genre_preferences` and `user_mode_preferences`.
- Games use soft deletes and are addressed by `slug` in URLs.

## Testing and code quality

```bash
composer test      # Pest feature tests (in-memory SQLite)
composer lint      # Laravel Pint
composer analyse   # Larastan
```

The tests run on in-memory SQLite, so they do not touch your MySQL database. The same three checks run in GitHub Actions.
