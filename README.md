# Nutech Paper Products CMS (PHP)

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

## Routes
- Public:
  - `/`
  - `/about-us`
  - `/contact-us`
  - `/product-catalog`
  - `/product/{slug}`
- Admin:
  - `/admin/login`
  - `/admin/dashboard`
  - `/admin/pages`
  - `/admin/products`
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

## Next Implementation Steps
1. Complete admin CRUD modules for Pages, Products, Media, SEO, Settings, Users.
2. Replace fallback data paths with full DB-backed workflows.
3. Finish script injection controls and sanitization policies.
4. Add activity logging across write operations.
5. Add stronger validation, pagination, and filtering in admin tables.

