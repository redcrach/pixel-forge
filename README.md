# Pixelforge Studio

An image generation studio on top of the Pixelforge API. Users buy credits, see the cost range before they generate, and are charged the exact provider cost plus a markup once the image succeeds. Failed generations are free. Operators get an admin panel with a daily spend, cost and profit report.

**Stack:** Laravel 13 (PHP 8.4), Inertia v3 + Vue 3 + TypeScript, Tailwind v4, Filament 5 (admin), Reverb (live updates), Pest.

## Contents

- [Running it locally](#running-it-locally)
    - [Option A: Docker](#option-a-docker-recommended)
    - [Option B: `composer run dev`](#option-b-composer-run-dev)
    - [Environment variables](#environment-variables)
    - [Receiving webhooks locally](#receiving-webhooks-locally)
- [Admin panel](#admin-panel)
- [Tests and code style](#tests-and-code-style)
- [Deploying with Docker](#deploying-with-docker)
- [Decisions](#decisions)

## Running it locally

Both options need a Pixelforge API key and its webhook signing secret (see [Environment variables](#environment-variables)).

### Option A: Docker (recommended)

Runs everything with the production database and services: PostgreSQL, Redis, a queue worker, the scheduler, Reverb and Vite, behind one NGINX proxy.

```bash
cp .env.example .env              # then fill in the Pixelforge and Reverb values below
composer install && npm install   # dependencies live on the host and are mounted in
php artisan key:generate

export UID GID=$(id -g)           # files the containers write stay owned by you
docker compose up -d --build
```

Open **http://pixelforge.localhost**. Migrations run when the app container starts. Code is mounted, so changes show up without a rebuild.

The variables in `compose.yaml` point Laravel at the containers (Postgres, Redis, Reverb), so you don't need to change `DB_*` or `REDIS_*` in your `.env`.

Useful commands:

```bash
docker compose exec app php artisan admin:create --email=you@example.com --password=secret
docker compose logs -f queue      # watch generations being submitted
docker compose down               # stop (add -v to also drop the database)
```

### Option B: `composer run dev`

Runs on your machine with SQLite and the database queue. Needs PHP 8.4 (with `bcmath` and `intl`), Composer and Node 22.

```bash
composer setup     # install, .env, app key, migrations, npm install, build
composer run dev
```

Open **http://localhost:8000**. `composer run dev` starts the web server, Vite, a queue worker, Reverb and the scheduler together.

### Environment variables

| Variable                                               | What it is                                                                                   |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------- |
| `PIXELFORGE_API_KEY`                                   | This environment's Pixelforge API key.                                                       |
| `PIXELFORGE_WEBHOOK_SECRET`                            | The key's webhook signing secret (`whsec_...`). Webhooks are rejected without it.            |
| `PIXELFORGE_MARKUP_BPS`                                | Markup on the provider cost, in basis points. `2500` = +25% (default).                       |
| `PIXELFORGE_WEBHOOK_TOLERANCE`                         | How many seconds old a webhook signature may be before it counts as a replay. Default `300`. |
| `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` | Any random values locally. They let the browser connect to Reverb for live updates.          |

### Receiving webhooks locally

Pixelforge reports results by calling `POST /webhooks/pixelforge`, so it needs to reach your machine. Point your API key's webhook URL at a tunnel (for example `cloudflared tunnel --url http://pixelforge.localhost`, or `http://localhost:8000` with Option B).

Without a tunnel, generations still finish: a scheduled job asks Pixelforge about any generation that has been quiet for 30 minutes (see [Decisions](#decisions)). It's just slow.

## Admin panel

The admin panel is at **`/admin`** (Filament). Only users with `users.is_admin` can open it; everyone else gets a 403.

Create an admin, or promote an existing user, with:

```bash
php artisan admin:create --email=admin@example.com --password=your-password
```

| Option       | Default                                                                                     |
| ------------ | ------------------------------------------------------------------------------------------- |
| `--email`    | `admin@pixelforge.test`. If the email already exists, that user is promoted.                |
| `--password` | A random password, printed once. An existing user keeps their password unless you pass one. |
| `--name`     | `Pixelforge Admin` (new accounts only)                                                      |

With Docker, prefix it with `docker compose exec app`.

What's in the panel:

- **Dashboard.** Generations, Pixelforge cost, credits purchased and net income, plus a chart of generations by status. You can filter by today, the last 7 days or the last 30 days. It refreshes every 10 seconds.
- **Usage report.** One row per UTC day: generations, tokens (input text, input images, output, reasoning), user spend, provider cost and profit. You can filter by date range and user. Days follow `completed_at`, the day Pixelforge bills, so each day's provider cost can be checked against `GET /v1/usage`.
- **Generations.** A read-only list of every user's generations. You can filter by status, user and creation date. A detail view shows tokens, costs and errors.

Signed-in admins also get an **[ Admin panel ]** link in the studio's side menu.

## Tests and code style

```bash
php artisan test        # Pest: pricing, credits ledger, webhooks, submission retries, admin
vendor/bin/pint         # PHP code style (pint --test to only check)
npm run types:check     # vue-tsc
npm run check           # frontend format and lint (vp check)
```

GitHub Actions run the full suite (`tests.yml`) and a Pint check (`lint.yml`) on every push to `main`.

## Deploying with Docker

`compose.production.yaml` runs the whole app on a single server behind Cloudflare. One image (the `production` target of the `Dockerfile`) runs every role: web, queue worker, scheduler and Reverb. Only the command changes. Code and compiled assets are baked into the image, and OPcache is on.

```
Cloudflare (proxied DNS, SSL "Full (strict)")
   └─ Caddy :443  (Cloudflare Origin Certificate)
        ├─ /app/*, /apps/*  → reverb
        └─ everything else  → app (PHP-FPM + NGINX)
   app · queue · scheduler · reverb · postgres · redis
```

1. **Cloudflare:** add a proxied (orange cloud) DNS record for your domain and set SSL/TLS to **Full (strict)**. Create an Origin Certificate (SSL/TLS → Origin Server) and save it as `docker/caddy/certs/origin.pem` and `origin-key.pem` on the server.
2. **Server:** copy the repository (or at least `compose.production.yaml`, `Dockerfile` and `docker/`), then create `.env` from `.env.production.example`. Set `APP_DOMAIN`, `APP_KEY`, the database and Redis passwords, the Reverb keys and the Pixelforge production key and webhook secret.
3. **Start:**

    ```bash
    docker compose -f compose.production.yaml build
    docker compose -f compose.production.yaml up -d
    docker compose -f compose.production.yaml exec app php artisan admin:create --email=... --password=...
    ```

    The web container runs migrations on start (one container at a time, behind a Redis lock) and caches config and routes.

4. **Firewall:** use the cloud provider's firewall, not `ufw`, because published Docker ports bypass `ufw`. Allow 443 from [Cloudflare's IP ranges](https://www.cloudflare.com/ips/) only, and 22 from your own IP.
5. **Pixelforge:** set the production key's webhook URL to `https://<APP_DOMAIN>/webhooks/pixelforge`.

To deploy a new version, pull, then run `build` and `up -d` again. The queue worker gets 60 seconds to finish its current job before it stops.

## Decisions

The brief ranks correctness of money first, then robustness, then UX, then code quality. Most decisions follow from that order.

### Money

- **Integer nanodollars everywhere.** 1 USD = 1,000,000,000 nanodollars, stored as `bigint`. Pixelforge's rates are decimal strings ("12.50" per 1M tokens), so they are converted with `bcmath`, never floats. Every cost is then an exact integer. Only the display rounds.
- **One formula, in PHP.** `CalculateProviderCost` and `ApplyMarkup` are the only place prices are computed. The cost estimate on the Generate page comes from a debounced `POST /generations/estimate`, so the browser never re-implements pricing.
- **The markup is in basis points.** Price = provider cost × (10,000 + `PIXELFORGE_MARKUP_BPS`) / 10,000. If that leaves a fraction of a nanodollar, it is rounded up and logged.
- **The real cost uses the generation's own rates.** Pixelforge freezes rates when a generation is created. The final charge uses those rates and the actual token usage, never the current `/pricing`.
- **Dollars are shown with 5 decimals,** like Pixelforge's usage report, so numbers can be compared at a glance. A cost range rounds its maximum up, so it never shows less than what will be held.

### Credits

- **A ledger, not a mutable balance.** Every change is a `credit_transactions` row: `purchase`, `hold`, `settle` or `release`. `users.balance_nano` is a cached running total, updated in the same database transaction under a row lock, so two requests can't spend the same credit.
- **Hold the maximum, then settle the actual cost.** When an image is submitted, the maximum possible cost (the top of the estimate) is held. On success, one `settle` row returns what's left of the hold and charges the exact price. On failure, a `release` row returns the whole hold. Failures are free, and settling twice is impossible.
- **Buying credits is simulated** (no payment provider), and is marked as sandbox in the UI.

### Talking to Pixelforge

- **Submission runs in a queued job.** The generation's own UUID is the `Idempotency-Key`, so every retry is safe and can never be billed twice.
- **Limits and overloads are retried, not shown as errors.**
    - 429 concurrency limit: the job is released and retried a few seconds later.
    - 429 rate limit and 503 overloaded: retried after `Retry-After`, with a little jitter.
    - Other 5xx errors: retried after a short random delay.
    - Permanent rejections (for example the daily limit or a validation error): the generation becomes `submission_failed` straight away and its hold is released, because waiting won't help.
    - Still failing after 15 minutes: the generation becomes `submission_failed` and its hold is released.
- **Webhooks are verified, deduplicated and ordered.**
    - Signature: HMAC-SHA256 over `"{timestamp}.{body}"`, with a 5-minute replay window. If the secret is missing, every webhook is rejected.
    - Duplicates: each event id is stored once (`webhook_events`), so a re-delivered event is ignored.
    - Order: statuses only move forward (`queued` → `processing` → `succeeded`/`failed`), so a late `processing` event can't undo a `succeeded` one.
    - A webhook can arrive before our own submit request has returned the Pixelforge id. It is matched through `metadata.order_id`, our generation id, which Pixelforge echoes back.
- **Polling is a fallback for missed webhooks.** Every 30 minutes, `PollStuckGenerations` asks Pixelforge about generations that have been quiet for 30 minutes. It handles at most 50 per run, to stay well inside the 60 requests per minute that every environment shares.
- **`/pricing` is cached** for an hour, and its last known value can still be used for up to a day.

### UX

- **Live updates instead of polling the server.** Generation changes and the balance are broadcast over Reverb and arrive as Inertia live props, so History and the header update the moment a webhook lands.
- **Every state has a screen:** waiting for a slot, queued, processing, succeeded, failed ("not charged"), image expired, insufficient balance, and toast messages for overloads and the daily limit.

### Admin

- **Filament on the normal login.** `is_admin` on users plus Filament's `canAccessPanel()`. A separate guard would add sessions and code without adding security.
- **The report reads only our own records.** It groups succeeded generations by the UTC date of `completed_at`, the day Pixelforge bills. Reviewers can compare each day's provider cost with `GET /v1/usage` themselves. The page doesn't call Pixelforge, so it stays fast and works when Pixelforge is down.
- **Token counts have their own columns** (`input_text_tokens`, `input_image_tokens`, `output_tokens`, `reasoning_tokens`) instead of living in JSON, so the report can `SUM()` them with plain SQL that works on PostgreSQL and SQLite (tests).

### Out of scope

- **Images are not copied to our own storage.** Pixelforge image URLs expire after 24 hours. The app tracks that and shows an "image expired" state instead of a broken image. Storing images before they expire was intentionally left out.
