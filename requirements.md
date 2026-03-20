# Nuteck Paper Products CMS Website Requirements

## 1) Project Overview
Build a production-ready PHP 8+ website and admin panel for Nuteck Paper Products on Hostinger shared hosting, using the approved screen designs in `screens/` as the visual source of truth.

The system must deliver:
- Public website with 5 pages:
  - Homepage
  - About Us
  - Contact Us
  - Product Catalog
  - Product Detail (dynamic by product slug)
- Admin dashboard for managing content, products, media, SEO, scripts, users, and theme/site settings.
- Clean URL routing with no `.php` in public URLs.
- Secure, maintainable architecture suitable for shared hosting.

## 2) Goals
- Match approved layouts exactly (no redesign).
- Make business content editable without code changes.
- Keep architecture simple, modular, and deployment-friendly for Hostinger.
- Ensure SEO controls are available at global and page/product levels.
- Support role-based admin access with clear permission boundaries.

## 3) Scope and Constraints
- Design is locked. Editable areas are content, media, SEO, script injections, and theme variables only.
- Global header and footer must always use the homepage design structure.
- Product details must be generated from CMS product data, not static files.
- Solution must run on Apache + PHP + MySQL/MariaDB in shared hosting context.

## 4) Functional Requirements

### 4.1 Public Website
- Render all 5 pages from CMS-backed data.
- Use homepage header/footer on every public page.
- Display navigation links mapped to CMS routes.
- Show product catalog with category/filter/sort behavior (minimum: category filtering and paging).
- Resolve product detail by slug (`/product/{slug}`).
- Include breadcrumbs on product detail page.
- Include contact/enquiry forms with server-side validation.
- Show SEO tags per page/product with fallback to global defaults.

### 4.2 Admin Dashboard
- Secure login/logout and session handling.
- Dashboard overview with quick stats (products, pages, media, recent updates).
- Content management by page and section.
- Product CRUD with publish/unpublish and ordering.
- Media library for upload/select/reuse/delete.
- SEO management for pages/products/global defaults.
- Script injection manager for head/footer code blocks.
- Site settings and theme variable management.
- User management with Admin and Content Editor roles.
- Profile management and password change.

### 4.3 Routing
- Public routes:
  - `/`
  - `/about-us`
  - `/contact-us`
  - `/product-catalog`
  - `/product/{slug}`
- Admin routes:
  - `/admin/login`, `/admin/logout`
  - `/admin/dashboard`
  - `/admin/pages/*`
  - `/admin/products/*`
  - `/admin/media/*`
  - `/admin/seo/*`
  - `/admin/settings/*`
  - `/admin/users/*`
- 404 route for unknown URLs.
- URL rewrite via `.htaccess` to hide `.php`.

### 4.4 Content Management
- Manage headings, subheadings, body text, CTA labels/links.
- Manage section visibility where defined by templates.
- Manage page-level structured content blocks via section keys.
- Manage homepage, about, contact, catalog intro, and product support content.
- Store content in DB (JSON/text), not hardcoded in templates.

### 4.5 Product Management
- Fields:
  - title, slug, short description, long description
  - specifications, features, applications
  - category, sort order, status, published_at
  - featured image + gallery images
  - SEO title/description/keywords/canonical/OG image
- Actions:
  - create/edit/delete/publish/unpublish
  - status filtering and search
  - slug auto-generation with uniqueness checks

### 4.6 Media Management
- Upload with MIME/type/size validation.
- Store metadata (filename, path, dimensions, alt text, caption).
- Preview and select reusable assets for content/products/SEO.
- Safe delete with dependency checks.

### 4.7 SEO and Scripts
- Per-page and per-product SEO fields.
- Global default SEO values.
- Canonical URL and robots tag support.
- Social metadata (OG title/description/image).
- Optional JSON-LD field support.
- Editable head scripts, verification tags, analytics snippets, and footer scripts.
- Script sanitization policy with trusted-admin controls.

### 4.8 Theme/Site Settings
- Site title, logo, favicon, contact details, address, social links.
- Theme color variables (primary, secondary/accent as configured).
- Default CTA text values.
- Settings apply without changing layout composition.

### 4.9 User and Role Management
- Roles:
  - Admin: full access including users/settings/SEO/scripts.
  - Content Editor: content/products/media editing only.
- Password hashing via `password_hash()`.
- Role checks enforced server-side on every protected route.

## 5) Non-Functional Requirements
- Performance:
  - Fast server-side render for first load.
  - Optimized image handling and dimensions metadata.
- Reliability:
  - Graceful error pages and logging.
  - Deterministic routing and fallback behavior.
- Maintainability:
  - Modular structure (`app`, `routes`, `templates`, `admin`).
  - Reusable partials/components.
- Security:
  - CSRF, input validation, output escaping, prepared statements.
- Compatibility:
  - PHP 8+, MySQL/MariaDB, Apache mod_rewrite, Hostinger shared hosting.

## 6) Public Website Page Inventory
- Homepage:
  - Hero, trust/benefit blocks, categories, enquiry CTA.
- About Us:
  - Hero, story, expertise, industries, quality commitment.
- Contact Us:
  - Contact form, company details, contact methods.
- Product Catalog:
  - Catalog hero, filters, grid listing, CTA.
- Product Detail:
  - Breadcrumbs, gallery, overview, specifications, features, enquiry form, related products.

## 7) Admin Module Inventory
- Authentication
- Dashboard
- Pages/Sections
- Products and Categories
- Media Library
- SEO Manager
- Scripts Manager
- Theme/Site Settings
- User Management
- Activity Logs (lightweight)

## 8) Roles and Permissions

| Capability | Admin | Content Editor |
|---|---:|---:|
| Login/Logout/Profile | Yes | Yes |
| Edit pages/sections | Yes | Yes |
| Manage products/categories | Yes | Yes |
| Upload/select media | Yes | Yes |
| Delete media in use | Yes (with warning) | No |
| Manage SEO global defaults | Yes | No |
| Manage page/product SEO | Yes | Yes |
| Manage head/footer scripts | Yes | No |
| Manage site/theme settings | Yes | No |
| Manage users/roles | Yes | No |
| View activity logs | Yes | Limited (own actions optional) |

## 9) Deployment Assumptions (Hostinger)
- Apache with `mod_rewrite` enabled.
- PHP 8.1+ recommended.
- MySQL/MariaDB available.
- Writable directories for uploads and logs.
- No root access; deployment via file manager/Git/FTP.
- Environment settings via `.env` loaded in app bootstrap.

## 10) Security Requirements
- CSRF tokens on all state-changing forms.
- Session regeneration on login.
- Strict server-side validation and sanitization.
- Escaping for all rendered dynamic output.
- PDO prepared statements only.
- Upload controls: whitelist MIME/extensions, file size limits, random filenames.
- Password policy and secure reset flow.
- Authorization checks on each admin endpoint.
- Basic audit logs for sensitive actions.

## 11) Scalability Considerations
- Keep schema normalized with selective JSON fields for flexible sections.
- Add indexes on frequently filtered fields (`slug`, `status`, `category_id`, timestamps).
- Optional pagination and query limits in admin grids.
- Media reuse via references to avoid duplication.
- Caching layer can be added later for heavy catalog traffic.

## 12) Future Extensions
- Multi-language content.
- Draft/revision workflow and scheduled publishing.
- Product comparison and downloadable datasheets.
- Inquiry CRM integration/email automation.
- API endpoints for external catalog syndication.
- Fine-grained permission matrix beyond two roles.

