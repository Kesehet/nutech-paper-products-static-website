# Nuteck Paper Products CMS - ER Diagram and Data Model

## 1) Modeling Strategy
- Use a normalized core schema for users, pages, products, media, and settings.
- Use `page_sections` as structured content blocks so templates stay locked but content stays editable.
- Use centralized `seo_meta` with `entity_type + entity_id` for page/product/global SEO.
- Use media mapping tables for reusable assets.
- Keep design/theme values in settings (grouped keys), not in templates.

## 2) Entity Overview
- `roles`: role master (Admin, Content Editor).
- `users`: admin users with role mapping.
- `pages`: managed public pages.
- `page_sections`: editable per-page content blocks.
- `product_categories`: catalog categories.
- `products`: product records with publish state and slug.
- `product_images`: product gallery + ordering.
- `media`: uploaded files and metadata.
- `seo_meta`: SEO records for global/page/product.
- `settings`: site, contact, and theme variables.
- `script_injections`: head/footer/admin-managed scripts.
- `navigation_items`: optional dynamic nav links.
- `contact_inquiries`: submitted public form entries.
- `activity_logs`: lightweight audit trail.
- `password_resets`: reset token storage.

## 3) Mermaid ER Diagram
```mermaid
erDiagram
    ROLES ||--o{ USERS : assigns
    USERS ||--o{ MEDIA : uploads
    USERS ||--o{ ACTIVITY_LOGS : performs
    USERS ||--o{ PASSWORD_RESETS : requests
    USERS ||--o{ PRODUCTS : creates_updates
    USERS ||--o{ PAGES : creates_updates
    USERS ||--o{ SETTINGS : updates
    USERS ||--o{ SCRIPT_INJECTIONS : updates

    PAGES ||--o{ PAGE_SECTIONS : contains
    PAGES ||--o{ NAVIGATION_ITEMS : links_to
    PAGES ||--o{ SEO_META : has

    PRODUCT_CATEGORIES ||--o{ PRODUCTS : classifies
    PRODUCTS ||--o{ PRODUCT_IMAGES : has
    PRODUCTS ||--o{ SEO_META : has

    MEDIA ||--o{ PRODUCT_IMAGES : reused_in
    MEDIA ||--o{ PAGE_SECTIONS : featured_asset
    MEDIA ||--o{ SEO_META : og_asset

    ROLES {
        int id PK
        varchar name
        varchar slug UK
        text description
        tinyint is_system
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        int id PK
        int role_id FK
        varchar full_name
        varchar email UK
        varchar password_hash
        tinyint is_active
        timestamp last_login_at
        timestamp created_at
        timestamp updated_at
    }

    PAGES {
        int id PK
        varchar title
        varchar slug UK
        varchar template_key
        tinyint is_system
        tinyint is_published
        int created_by FK
        int updated_by FK
        timestamp created_at
        timestamp updated_at
    }

    PAGE_SECTIONS {
        int id PK
        int page_id FK
        varchar section_key
        varchar section_label
        json content_json
        int featured_media_id FK
        tinyint is_visible
        int sort_order
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_CATEGORIES {
        int id PK
        varchar name
        varchar slug UK
        text description
        int sort_order
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        int id PK
        int category_id FK
        varchar title
        varchar slug UK
        text short_description
        longtext long_description
        json specifications_json
        json features_json
        json applications_json
        int featured_image_id FK
        int sort_order
        enum status
        timestamp published_at
        int created_by FK
        int updated_by FK
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_IMAGES {
        int id PK
        int product_id FK
        int media_id FK
        varchar alt_text
        tinyint is_primary
        int sort_order
        timestamp created_at
    }

    MEDIA {
        int id PK
        varchar disk
        varchar file_name
        varchar original_name
        varchar storage_path
        varchar mime_type
        int size_bytes
        int width
        int height
        varchar alt_text
        varchar caption
        int uploaded_by FK
        timestamp created_at
    }

    SEO_META {
        int id PK
        varchar entity_type
        int entity_id
        varchar meta_title
        text meta_description
        varchar meta_keywords
        varchar canonical_url
        varchar robots
        varchar og_title
        text og_description
        int og_image_id FK
        json schema_json
        timestamp created_at
        timestamp updated_at
    }

    SETTINGS {
        int id PK
        varchar setting_group
        varchar setting_key
        longtext setting_value
        varchar value_type
        tinyint is_public
        int updated_by FK
        timestamp created_at
        timestamp updated_at
    }

    SCRIPT_INJECTIONS {
        int id PK
        varchar location
        varchar label
        longtext script_content
        tinyint is_active
        int updated_by FK
        timestamp created_at
        timestamp updated_at
    }

    NAVIGATION_ITEMS {
        int id PK
        varchar menu_key
        varchar label
        varchar href
        int page_id FK
        tinyint open_in_new_tab
        int sort_order
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }

    CONTACT_INQUIRIES {
        int id PK
        varchar full_name
        varchar email
        varchar phone
        varchar company_name
        varchar inquiry_type
        text message
        int product_id FK
        varchar source_page
        varchar status
        timestamp created_at
    }

    ACTIVITY_LOGS {
        int id PK
        int user_id FK
        varchar action
        varchar entity_type
        int entity_id
        json before_json
        json after_json
        varchar ip_address
        varchar user_agent
        timestamp created_at
    }

    PASSWORD_RESETS {
        int id PK
        int user_id FK
        varchar token_hash
        timestamp expires_at
        timestamp used_at
        timestamp created_at
    }
```

## 4) Key Constraints and Indexes
- Unique:
  - `roles.slug`
  - `users.email`
  - `pages.slug`
  - `product_categories.slug`
  - `products.slug`
  - `settings (setting_group, setting_key)`
  - `seo_meta (entity_type, entity_id)` for one SEO row per entity.
- Foreign keys:
  - all `*_id` references constrained where practical.
- Check constraints (or app-layer validation if DB lacks support):
  - `products.status` in (`draft`, `published`, `archived`).
  - `script_injections.location` in (`head_start`, `head_end`, `body_end`).
  - `seo_meta.entity_type` in (`global`, `page`, `product`).
- Indexes:
  - `products (status, category_id, sort_order)`
  - `products (slug)`
  - `pages (slug, is_published)`
  - `page_sections (page_id, section_key)`
  - `media (created_at)`
  - `activity_logs (user_id, created_at)`

## 5) Role and Permission Relationship
- `users.role_id -> roles.id` defines default capability level.
- Permission enforcement is policy-based in code:
  - Admin routes gated to `roles.slug = admin`
  - Editor-safe routes allow `admin|content_editor`

## 6) Content and SEO Storage Notes
- `page_sections.content_json` stores structured fields per section (headings, text, CTA labels/links, toggles).
- `seo_meta` supports:
  - global defaults (`entity_type=global`, `entity_id=0`)
  - page-level overrides (`entity_type=page`, `entity_id=pages.id`)
  - product-level overrides (`entity_type=product`, `entity_id=products.id`)
- Render layer resolves SEO by priority:
  1. Entity SEO
  2. Global SEO defaults
  3. Template hard fallback

## 7) Media and Theme Mapping
- All image references use `media.id` to avoid duplicated uploads.
- Theme values use `settings` rows, e.g.:
  - `theme.primary_color`
  - `theme.secondary_color`
  - `branding.logo_media_id`
  - `branding.favicon_media_id`
  - `site.title`
  - `site.contact_email`

