# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Docker
```bash
docker compose up -d                  # Start all services (PHP, Nginx, MySQL)
docker compose up -d --build php      # Rebuild and restart PHP container
docker compose down                   # Stop all services
docker compose exec php composer install   # Install/update PHP dependencies
docker compose exec php php think run      # Start ThinkPHP dev server on :8000
```

### Frontend (admin-system/)
```bash
cd admin-system && npm run dev         # Vite dev server with HMR
cd admin-system && npm run build       # Production build → dist/
```
Frontend dev server runs on `http://localhost:5173` by default, then built output is served by Nginx.

### Commands
- `php think run` — start ThinkPHP dev server
- `php think migrate:run` — run database migrations
- `php think clear` — clear runtime cache
- `php think optimize:schema` — optimize DB schema cache

## Architecture

### Docker Setup (docker compose up -d)
Three services orchestrated via docker-compose:
- **php** — PHP 8.2-fpm with Composer, mounts `./src` to `/var/www`
- **nginx** — Two virtual hosts on port 80:
  - `api.bailitop.com` → ThinkPHP API (routes via `index.php`)
  - `tp.bailitop.com` → Admin SPA (serves `admin-system/dist/`)
- **mysql** — MySQL 8.0, auto-initializes from `sql/init.sql`

### Backend (src/) — ThinkPHP 8
Standard ThinkPHP directory layout. Key areas:

- **`config/database.php`** — DB host is the Docker container name `tp_mysql` (not localhost)
- **`route/app.php`** — All API routes defined here, grouped by resource (`api/user`, `api/role`, `api/object`, etc.)
- **`app/controller/api/`** — API controllers (Auth, User, Role, Menu, Objects, ObjectField, ObjectTrigger, Page, Settings)
- **`app/middleware/JwtMiddleware.php`** — JWT auth middleware using `firebase/php-jwt`; extracts Bearer token, decodes with HS256, attaches `$request->user`. Applied per-route, not globally.
- **`app/BaseController.php`** — All controllers should extend this; provides `$request`, `$app`, and `validate()` helper.
- **`.env`** — Environment config (JWT_SECRET, DB credentials). `.example.env` is the template.

### Frontend (admin-system/) — Vue 3 + Vite + Ant Design Vue
- **`src/router/index.ts`** — All routes; Layout wrapper with nested children
- **`src/views/system/`** — Core CRUD pages: user, role, permission, object, menu, page
- **`src/views/page/objectList.vue`** — Dynamic object listing page
- **`src/views/login/index.vue`** — Login page
- **`src/utils/request.ts`** — Axios wrapper (interceptors, token injection)
- **`src/store/user.ts`** — Pinia store for auth state
- `@` path alias resolves to `src/`

### Nginx Routing
- PHP requests: URL rewriting → `index.php?s=$1` (ThinkPHP pathinfo mode)
- SPA requests: `try_files` with fallback to `index.html`
- CORS headers are set at Nginx level for all `/api/` responses

### Database
- `sql/init.sql` runs on first MySQL start
- `thinkphp.sql` in root is a full DB dump for restoration
