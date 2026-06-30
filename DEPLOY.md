# Deploying BoxerOS to production

Everything in the app is production-ready in code. These are the host-side steps to go live.
Pick **one** host path (A = managed platform, easiest; B = your own VPS).

---

## A) Easiest — a managed platform (recommended to start)
Use **[Laravel Cloud](https://cloud.laravel.com)**, **[Railway](https://railway.app)**, or **[Render](https://render.com)**.
They handle the server, HTTPS, and (usually) managed MySQL + backups.

1. Push this repo to GitHub (one repo — see "Unify the copies" below).
2. Create a project on the platform, connect the GitHub repo.
3. Add a **MySQL** database (the platform gives you the DB credentials).
4. Set the **environment variables** from `.env.production.example` (especially `APP_KEY` via `php artisan key:generate`, `ANTHROPIC_API_KEY`, and the `MAIL_*` SMTP values).
5. Set the build/release commands:
   - Build: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
   - Release: `php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. Point your **domain** at the platform (they walk you through DNS + free SSL).

## B) Your own VPS (Hetzner / DigitalOcean) — more control
Use **[Laravel Forge](https://forge.laravel.com)** ($12/mo) on a $5–10/mo server — it sets up PHP, Nginx, MySQL, SSL (Let's Encrypt), and deploys from GitHub with one click. Then set the same env vars and deploy command as above.

---

## Required env values
Copy `.env.production.example` → `.env` and fill in:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.com`
- `APP_KEY` → `php artisan key:generate`
- `DB_*` → your managed MySQL
- `ANTHROPIC_API_KEY` → your key (secret)
- `MAIL_*` → Brevo/Resend SMTP (so verification + password-reset emails send)

## After first deploy
```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
```

## Backups (already built in)
- A daily DB backup command exists: `php artisan boxeros:backup` (writes to `storage/app/backups`, keeps 7).
- It's scheduled in `routes/console.php`. For it (and any scheduled work) to run, add **one cron entry** on the server:
  ```
  * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
  ```
- Also enable your **host's managed database backups** as the off-site safety net.

## Error monitoring (optional, ~5 min)
- Create a free **[Sentry](https://sentry.io)** project → `composer require sentry/sentry-laravel` → add `SENTRY_LARAVEL_DSN` to `.env`. Until then, errors are written to daily logs (`storage/logs`).

## Unify the two copies (do this once, before deploy)
Right now there's a Windows copy and a WSL2 copy synced by hand. Before deploying:
1. Pick one as the source of truth, push it to a **private GitHub repo**.
2. Deploy *from GitHub* (the platform/Forge pulls from there).
3. From then on: edit → commit → push → auto-deploy. No more manual file copying.

## Pre-launch checklist
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] HTTPS working (auto on managed platforms / Forge)
- [ ] `ANTHROPIC_API_KEY` set + a spend cap on the Anthropic console
- [ ] `MAIL_*` set — register a test account and confirm the verification email arrives
- [ ] Migrations run; you can log in; onboarding shows for a fresh account
- [ ] Daily backup cron added; run `php artisan boxeros:backup` once to confirm it works
- [ ] Legal pages reachable: /terms, /privacy, /disclaimer
