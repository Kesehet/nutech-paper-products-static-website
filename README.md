# Nuteck Paper Products CMS (PHP)

CMS-driven PHP website + admin panel for Hostinger-compatible Apache/PHP hosting.

## Current Status
- Phase 1 planning docs completed:
  - `requirements.md`
  - `er_diagram.md`
  - `design.md`
- Core app scaffolding implemented:
  - Front controller routing
  - Public pages with clean URLs
  - Admin login/dashboard/module scaffolds
  - DB schema + seed SQL
  - `.env`-based configuration
  - bootstrap auto-sync (schema/seed) for first-time install

## Tech Stack
- PHP 8+
- MySQL / MariaDB
- Apache (`mod_rewrite`)
- PDO
- Tailwind CDN (for initial screen-faithful rendering pass)

## Project Structure
- `app/` - core, controllers, services, helpers
- `routes/` - public and admin routes
- `templates/` - layouts, partials, pages, admin views
- `public/` - web entry, static assets, uploads
- `database/` - schema + seed SQL

## Local Setup (XAMPP/Hostinger-like)
1. Copy env file:
   - `copy .env.example .env`
2. Create database:
   - `nutech_cms`
3. Import SQL files in order:
   - `database/schema.sql`
   - `database/seeders/seed.sql`
4. Update `.env` DB credentials.
5. Ensure writable directories:
   - `public/uploads`
   - `storage/logs`
6. Serve project via Apache root:
   - Root strategy is supported using project-level `.htaccess`.
   - If document root can be changed, point it to `public/`.

### URL/Base Path Configuration
- `APP_URL`: primary site URL (used for host + optional absolute URL generation).
- `APP_BASE_PATH`: URL path prefix where app is mounted.
  - Example subfolder deploy: `/nutech-paper-products-static-website`
  - Example root deploy: leave empty
- `APP_USE_ABSOLUTE_URLS`:
  - `false` (default): generate root-relative URLs like `/assets/...`
  - `true`: generate absolute URLs like `https://example.com/assets/...`
- `APP_REDIRECT_TO_BASE_PATH`:
  - `true` enables 301 redirect to canonical `APP_BASE_PATH`.
  - Keep `false` until your server can serve the app on that canonical path.

### Optional Auto-Sync (Enabled by Default)
- On app boot, if `AUTO_SYNC_ON_BOOT=true`, the app checks DB state.
- If `users` table is missing or has zero rows, it automatically runs:
  - `database/schema.sql`
  - `database/seeders/seed.sql`
- `AUTO_SYNC_CREATE_DATABASE` defaults to `false` (recommended for shared hosting).
  - Set it `true` only when DB user has create-database permission.
- Auto-sync writes failures to `storage/logs/auto_sync.log`.

## Routes
- Public:
  - `/`
  - `/about-us`
  - `/contact-us`
  - `/product-catalog`
  - `/product/{slug}`
  - `/blogs`
  - `/blogs/{slug}`
- Admin:
  - `/admin/login`
  - `/admin/dashboard`
  - `/admin/pages`
  - `/admin/products`
  - `/admin/blogs`
  - `/admin/media`
  - `/admin/seo`
  - `/admin/settings`
  - `/admin/users`

## Default Admin Seed
- Email: `admin@nutech.local`
- Password: `ChangeMe123!`

Change this immediately in production.

## Deployment Notes (Hostinger)
- Upload all project files.
- Ensure `.htaccess` is enabled and `mod_rewrite` works.
- Set production env values in `.env`.
- Import DB schema and seed data.
- Configure secure permissions for uploads/log directories.
- To remove `/nutech-paper-products-static-website` from public URLs:
  - Preferred: point domain document root to this project directory (or its `public/` folder).
  - Then set `APP_BASE_PATH=` (empty).
  - Optionally set `APP_REDIRECT_TO_BASE_PATH=true` to 301 old subfolder links to root.

## Next Implementation Steps
1. Complete admin CRUD modules for Pages, Products, Media, SEO, Settings, Users.
2. Replace fallback data paths with full DB-backed workflows.
3. Finish script injection controls and sanitization policies.
4. Add activity logging across write operations.
5. Add stronger validation, pagination, and filtering in admin tables.
