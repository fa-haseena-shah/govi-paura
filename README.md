# Govi Paura 🌾🚜🥕

A web-based B2B farm-to-retail marketplace platform connecting farmers directly with retailers/buyers, cutting out the middleman as the final computing project of our HND.

This README is written for the 5 of us team members — everything below is already set up on the repo.

## Architecture

The project is **decoupled**: backend and frontend run as two separate things during development.

- **`backend/`** — Runs via `php artisan serve`.
- **`frontend/`** — HTML, CSS and JavaScript. Runs via the VS Code **Live Server** extension. 

## Tech Stack

- **Backend:** PHP 8.2+, Laravel, MySQL, Laravel Sanctum (API token auth)
- **Frontend:** HTML, CSS, JavaScript (no framework), VS Code Live Server
- **Architecture:** SOA-style modular monolith

## Prerequisites

Install these first:

- PHP >= 8.2
- Composer
- MySQL (through WAMP)
- Git
- VS Code with the **Live Server** extension

## Getting Started — Backend

1. Clone the repo:
   ```bash
   git clone https://github.com/<owner>/govipaura.git
   cd govipaura/backend
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate your app key:
   ```bash
   php artisan key:generate
   ```
5. Create an empty MySQL database named `govipaura_db` (e.g. via phpMyAdmin).
6. Open `.env` and confirm/set:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=govipaura_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
7. Run migrations:
   ```bash
   php artisan migrate
   ```
   This creates all the default Laravel tables plus Sanctum's `personal_access_tokens` table. (The MySQL "key too long" error some setups hit is already fixed in `AppServiceProvider.php` — you shouldn't see it.)
8. Start the API server:
   ```bash
   php artisan serve
   ```
   API is now live at `http://localhost:8000`.

## Getting Started — Frontend

1. Open the `frontend/` folder in VS Code.
2. Right-click `index.html` → **"Open with Live Server"**.
3. Note the port shown (usually `http://127.0.0.1:5500`). If yours is different, tell whoever's maintaining `backend/config/cors.php` so your origin gets added — see below.

## Running Day-to-Day

Two processes, two terminals, every time you work on this:

```bash
# Terminal 1 — backend
cd backend
php artisan serve

# Terminal 2 — frontend
# Click "Go Live" in VS Code (bottom-right status bar)
```

## Auth & CORS (already configured — reference only)

**CORS** — `backend/config/cors.php` already allows the standard Live Server origins:
```php
'allowed_origins' => ['http://127.0.0.1:5500', 'http://localhost:5500'],
```
If your Live Server opens on a different port, add it to this array.

**Auth** — Laravel Sanctum, **token-based** (not cookies, since frontend and backend are different origins). Every protected request needs:
```js
headers: { Authorization: `Bearer ${token}` }
```
The `User` model already has `HasApiTokens` — issuing/checking tokens works once Auth endpoints are built.

## Backend Structure (Modular Monolith)

Each business domain is self-contained under `backend/app/Domains/`:

```
app/Domains/
  Auth/           Models/  DTOs/  Services/  Http/Controllers/  Http/Requests/
  Verification/
  Listing/
  Bidding/
  Order/
  Payment/
  Delivery/
  Rating/
  PriceHistory/
  Notification/
  Support/
  Subscription/
  Admin/
```

Pattern for every domain: **Model → DTO → Service → Controller**. Controllers stay thin (validate via `FormRequest`, call a Service, return `JsonResponse`). Business logic lives in Services, not Controllers.

`App\Models\User` stays in its default Laravel location (`app/Models/User.php`) — not moved into `Domains/`, since Laravel's own internals expect it there.

Folders that don't have real files yet contain a `.gitkeep` placeholder — **delete it** the first time you add a real file to that folder.

## Branching Strategy

- **`main`** — always stable/demo-ready. Protected. Only updated via PR from `dev`.
- **`dev`** — integration branch. All feature branches merge here first.
- **`feature/<domain>-<short-description>`** — one branch per task, created off `dev`.

### Workflow for every task

```bash
git checkout dev
git pull
git checkout -b feature/bidding-submit-bid

# ...work, commit as you go...

git push -u origin feature/bidding-submit-bid
```
Open a Pull Request into `dev` on GitHub. Get it reviewed by at least one other team member before merging. Before each milestone/demo, `dev` gets merged into `main` via PR and tagged (`v0.1`, `v0.2`, ...).

### Commit messages

Keep them short and specific: `feat: add bid submission endpoint`, `fix: correct listing status enum`, `chore: scaffold Delivery domain folders`.

## Environment Notes

- `.env` is **never committed**. Only `.env.example` is tracked — if you change a config value everyone needs, update `.env.example` too and mention it to the team.
- Everyone runs migrations against their **own local MySQL** — schema is defined in code (`database/migrations/`) and versioned in Git, so there's no shared live database to keep in sync manually.
