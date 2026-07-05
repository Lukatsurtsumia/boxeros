<h1 align="center">🥊 BoxerOS</h1>

<p align="center">
  <strong>Your corner, in your pocket.</strong><br>
  A mobile-first training companion for professional boxers — track weight, nutrition,
  hydration and fights, and get real coaching from an AI that actually knows your numbers.
</p>

<p align="center">
  Laravel&nbsp;12 · Livewire&nbsp;4 · Tailwind&nbsp;CSS · MySQL · Anthropic&nbsp;Claude · English&nbsp;🇬🇧 / French&nbsp;🇫🇷
</p>

---

## What it is

BoxerOS is a personal "operating system" for a fighter's camp — everything from the weigh-in to
the final bell in one place. At the center is **CORNER**, an AI coach that reads your *real* logs
(this week's training, current weight vs. your goal, your meals, days until your next fight) and
tells you exactly what to do next.

## Features

| | |
|---|---|
| 🏠 **Dashboard** | Fighter + next-fight countdown, today's snapshot, weight trend & goal, weekly review, top CORNER coaching flag |
| 👤 **Boxer Profile** | Nickname, weight class, record (W/L/D), gym, trainer, stance, bio, avatar |
| 📓 **Daily Log** | Weigh-ins, water quick-add, multi-session training, sleep, sugar/alcohol/caffeine, mood & energy |
| 🍽️ **Meal Tracker** | Log meals by name; CORNER estimates the calories, you confirm or adjust |
| 📋 **Weekly Plan** | CORNER builds a periodised training + nutrition week; the dashboard tracks adherence |
| 📅 **Fight Calendar** | Countdown to your next bout + full win/loss record |
| 🤖 **CORNER** | Claude-powered coach with full boxer context — ask anything, anytime |

- 🌍 **Bilingual** — the entire UI *and* CORNER switch between **English** and **French**, per user.
- 📆 **Personal weeks** — each fighter's week starts on their sign-up day, so the first recap reflects a full week.

## Screenshots

<img width="1882" height="1028" alt="Capture d&#39;écran 2026-07-05 080921" src="https://github.com/user-attachments/assets/55fd1c91-dc27-4ba3-bc21-2214096c2fd5" />
<img width="1902" height="1022" alt="Capture d&#39;écran 2026-07-05 083925" src="https://github.com/user-attachments/assets/376c32d5-a227-4be8-b927-64321a4ce50f" />

<img width="1915" height="1022" alt="Capture d&#39;écran 2026-07-05 084009" src="https://github.com/user-attachments/assets/9d97380a-f555-48c5-b55b-a7425a36fce5" />
 
<img width="1918" height="1007" alt="Capture d&#39;écran 2026-07-05 083503" src="https://github.com/user-attachments/assets/33c3f6cb-0894-42c0-96a7-d6fb248bc93b" />
<img width="1912" height="1020" alt="Capture d&#39;écran 2026-07-05 083406" src="https://github.com/user-attachments/assets/62cc70cc-b4f8-429e-ae01-3a36720602e3" />
<img width="1917" height="1036" alt="Capture d&#39;écran 2026-07-05 081559" src="https://github.com/user-attachments/assets/64c3f0a1-e594-4502-b44f-7e3e2f53389a" />
<img width="1897" height="1022" alt="Capture d&#39;écran 2026-07-05 081208" src="https://github.com/user-attachments/assets/1e640aaf-fd8a-48c0-92f8-a6cb386d896a" />
<img width="1918" height="1022" alt="Capture d&#39;écran 2026-07-05 081142" src="https://github.com/user-attachments/assets/de6af457-a5f9-4990-b00d-c4bee9d99ce3" />
<img width="1907" height="1037" alt="Capture d&#39;écran 2026-07-05 081424" src="https://github.com/user-attachments/assets/6dab3199-e845-4ce2-9566-b9089cca3e27" />



## Tech stack

- **Backend:** Laravel 12 · PHP 8.2+ · MySQL
- **Frontend:** Livewire 4 · Tailwind CSS · Vite · Alpine (Livewire-bundled)
- **Auth:** Laravel Breeze (Blade)
- **AI:** Anthropic Claude (Sonnet for chat/plans, Haiku for background tasks) — one entry point in `app/Support/Corner.php`
- **Local dev:** Docker via Laravel Sail
- **Production:** Coolify (self-hosted PaaS) on a Hetzner VPS — see [`DEPLOY.md`](DEPLOY.md)

## Local development

Requires Docker (Sail), Node 22, and Composer 2.

```bash
git clone https://github.com/Lukatsurtsumia/boxeros.git
cd boxeros
composer install && npm install
cp .env.example .env
php artisan key:generate

docker compose up -d                 # HTTP :8090 · Vite :5174 · MySQL :3320
docker compose exec laravel.test php artisan migrate
npm run build                        # or `npm run dev` for hot reload
```

App → **https://boxeros.app**

## Tests

```bash
docker compose exec laravel.test php artisan test
```

## Deployment

Production runs on **Coolify** (self-hosted) on a Hetzner VPS, deploying straight from this repo.
Full step-by-step runbook and the production env template:
**[`DEPLOY.md`](DEPLOY.md)** · **`.env.production.example`**.

## Environment variables

| Variable | Purpose |
|---|---|
| `APP_KEY` | Laravel app key — generate with `php artisan key:generate` |
| `APP_URL` | Base URL (`http://localhost:8090` local / `https://boxeros.app` prod) |
| `DB_*` | MySQL connection |
| `ANTHROPIC_API_KEY` | Enables CORNER chat, meal estimates, plan/recap generation *(set a spend cap!)* |
| `MAIL_*` | SMTP for email verification & password resets (e.g. Brevo) |

---

<p align="center"><sub>© 2026 BoxerOS — private project. All rights reserved.</sub></p>
