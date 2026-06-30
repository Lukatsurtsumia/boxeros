# BoxerOS

A mobile-first, dark-themed web app for boxers to track health, performance, and get AI coaching.

## Features

- **Dashboard** — fighter + next-fight countdown, today snapshot, weight trend + goal, weekly review, CORNER coaching flag
- **Boxer Profile** — nickname, weight class, record (W/L/D), gym, trainer, stance, bio, avatar upload
- **Daily Log** — weigh-ins, water (+250ml quick-add), multi-session training, sleep, sugar/alcohol/caffeine, mood/energy
- **Meal Tracker** — log meals; CORNER estimates the calories, you confirm or adjust
- **Plan** — CORNER builds a periodised weekly training/nutrition plan; the dashboard tracks adherence
- **Fight Calendar** — upcoming fight countdown and full fight history
- **CORNER Chatbot** — Claude AI coach with full boxer context (profile, today's log, meals, next fight)
- **Knowledge Base** — admin-curated coaching references
- **Bilingual** — full English/French UI (per-user) with CORNER replying in the chosen language

## Stack

- **Backend:** Laravel 12, PHP 8.4, MySQL
- **Frontend:** Livewire 4, Tailwind CSS, Vite
- **Auth:** Laravel Breeze (Blade)
- **AI:** Anthropic Claude (`claude-sonnet-4-6`) via direct HTTP
- **Infrastructure:** Docker via Laravel Sail (WSL2 Ubuntu 24.04)

## Getting Started

### Prerequisites

- WSL2 with Ubuntu 24.04 LTS
- Docker Desktop (with WSL2 integration enabled)
- Node 22 LTS (via NVM) and Composer 2.9 inside WSL2

### Setup

```bash
# Clone and enter the project (inside WSL2)
git clone <repo-url> ~/projects/healthy_life
cd ~/projects/healthy_life

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```env
DB_DATABASE=boxeros
DB_USERNAME=sail
DB_PASSWORD=password

ANTHROPIC_API_KEY=your-key-here
```

### Running with Docker/Sail

```bash
# Start containers (HTTP: 8090, Vite: 5174, MySQL: 3320)
docker compose up -d

# Run migrations
docker compose exec laravel.test php artisan migrate

# Build frontend assets (hot reload)
npm run dev
```

App is available at **http://localhost:8090**

### Artisan commands

```bash
# Via docker compose
docker compose exec laravel.test php artisan <command>

# Via Sail alias
./vendor/bin/sail artisan <command>
```

## Running Tests

```bash
./vendor/bin/sail artisan test
# or
php artisan test
```

## Environment Variables

| Variable | Description |
|---|---|
| `ANTHROPIC_API_KEY` | Enables CORNER chat, meal calorie estimates, and AI plan/weekly-recap generation |
| `DB_DATABASE` | Database name (`boxeros`) |
| `APP_URL` | Set to `http://localhost:8090` for local dev |
