INSERT INTO roles (name, slug, description, is_system) VALUES
('Administrator', 'admin', 'Full access to all modules', 1),
('Content Editor', 'content_editor', 'Can manage content, products, and media', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO users (role_id, full_name, email, password_hash, is_active)
SELECT r.id, 'System Admin', 'admin@nutech.local', '$2y$10$R5UrYP9bqILZxskWUz3hwORS6D0QrH.nDdHXpSTbQ0Vmm.C5rf0ha', 1
FROM roles r
WHERE r.slug = 'admin'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@nutech.local');

INSERT INTO pages (title, slug, template_key, is_system, is_published) VALUES
('Homepage', 'home', 'home', 1, 1),
('About Us', 'about-us', 'about', 1, 1),
('Contact Us', 'contact-us', 'contact', 1, 1),
('Blogs', 'blogs', 'blogs', 1, 1),
('Product Catalog', 'product-catalog', 'product_catalog', 1, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), template_key = VALUES(template_key), is_published = VALUES(is_published);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'home.hero', 'Homepage Hero', JSON_OBJECT(
    'eyebrow', 'Industrial Excellence Since 1995',
    'heading', 'Trusted Manufacturer of Self Adhesive and Release Papers',
    'description', 'Premium paper and coating solutions for packaging, labeling, and high-volume industrial use.',
    'primary_cta_label', 'View Products',
    'primary_cta_link', '/product-catalog'
), 1, 1
FROM pages p
WHERE p.slug = 'home'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'home.hero'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'home.why_choose_us', 'Why Choose Us', JSON_OBJECT(
    'heading', 'Why Choose Us',
    'description', 'Trusted partner for consistent quality, timely bulk supply, and application-driven engineering.',
    'item_1_title', 'Trusted Since 1995',
    'item_1_description', 'Long-term manufacturing experience across industries.',
    'item_2_title', 'Consistent Quality',
    'item_2_description', 'Controlled processes with strict quality checks.',
    'item_3_title', 'Bulk Supply',
    'item_3_description', 'Scalable production for recurring enterprise demand.',
    'item_4_title', 'Competitive Pricing',
    'item_4_description', 'Strong value through optimized sourcing and processes.'
), 1, 2
FROM pages p
WHERE p.slug = 'home'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'home.why_choose_us'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'home.cta', 'Homepage CTA', JSON_OBJECT(
    'heading', 'Send an Enquiry',
    'description', 'Have a specific requirement or looking for a bulk quote? Our experts are ready to assist you with the right paper solutions.',
    'phone_label', 'Phone',
    'email_label', 'Email',
    'address_label', 'Address',
    'name_label', 'Name',
    'name_placeholder', 'Full Name',
    'email_form_label', 'Email',
    'email_placeholder', 'email@example.com',
    'product_interest_label', 'Product Interest',
    'product_interest_placeholder', 'Select a product',
    'message_label', 'Message',
    'message_placeholder', 'Your requirements...',
    'submit_label', 'Send Enquiry'
), 1, 3
FROM pages p
WHERE p.slug = 'home'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'home.cta'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'about.hero', 'About Hero', JSON_OBJECT(
    'badge', 'Precision & Quality',
    'heading', 'Pioneering Paper Excellence Since 1995',
    'description', 'Nuteck Paper Products delivers dependable self-adhesive and release solutions for B2B industries.',
    'image_path', 'https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?auto=format&fit=crop&w=1600&q=80',
    'image_alt', 'Industrial paper manufacturing facility with large machinery'
), 1, 1
FROM pages p
WHERE p.slug = 'about-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'about.hero'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'about.story', 'Our Story', JSON_OBJECT(
    'heading', 'Our Story',
    'description_1', 'Established in New Delhi in 1995, Nuteck has been at the forefront of the paper industry for over two decades. What began as a local vision has grown into a national powerhouse in paper processing.',
    'description_2', 'Nuteck Paper Products began its journey with a vision to provide high-quality paper solutions. Over the years, we have grown into a leading manufacturer, known for our reliability and innovation in the B2B sector.',
    'years_value', '28+',
    'years_label', 'Years of Experience',
    'clients_value', '1500+',
    'clients_label', 'Clients Served',
    'image_path', 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=1200&q=80',
    'image_alt', 'Close up of high quality adhesive paper rolls'
), 1, 2
FROM pages p
WHERE p.slug = 'about-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'about.story'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'about.expertise', 'Our Expertise', JSON_OBJECT(
    'heading', 'Our Expertise',
    'description', 'Specialized manufacturing of self-adhesive papers and specialized films tailored for industrial requirements.',
    'item_1_icon', 'precision_manufacturing',
    'item_1_title', 'Self-Adhesive Solutions',
    'item_1_description', 'Premium quality Chromo, Mirror Coat, and Woodfree self-adhesive papers for various surfaces.',
    'item_2_icon', 'layers',
    'item_2_title', 'Specialized Films',
    'item_2_description', 'BOPP, PE, and PET films designed for durability and high-performance labeling applications.',
    'item_3_icon', 'architecture',
    'item_3_title', 'Custom Coating',
    'item_3_description', 'Advanced siliconizing and adhesive coating techniques tailored to specific B2B needs.'
), 1, 3
FROM pages p
WHERE p.slug = 'about-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'about.expertise'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'about.industries', 'Industries Served', JSON_OBJECT(
    'heading', 'Industries Served',
    'description', 'Our products power critical operations across diverse industrial landscapes.',
    'cta_label', 'Explore Applications',
    'cta_link', '/contact-us',
    'item_1_title', 'Packaging',
    'item_1_image', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=900&q=80',
    'item_1_alt', 'Modern logistics and packaging warehouse',
    'item_2_title', 'Printing',
    'item_2_image', 'https://images.unsplash.com/photo-1589365278144-c9e705f843ba?auto=format&fit=crop&w=900&q=80',
    'item_2_alt', 'Commercial printing machine',
    'item_3_title', 'Labeling',
    'item_3_image', 'https://images.unsplash.com/photo-1609833975787-5c143f373a97?auto=format&fit=crop&w=900&q=80',
    'item_3_alt', 'Product containers with adhesive labels',
    'item_4_title', 'Pharma & FMCG',
    'item_4_image', 'https://images.unsplash.com/photo-1581091215367-59ab6dcef782?auto=format&fit=crop&w=900&q=80',
    'item_4_alt', 'High-tech manufacturing facility interior'
), 1, 4
FROM pages p
WHERE p.slug = 'about-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'about.industries'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'about.quality', 'Quality Commitment', JSON_OBJECT(
    'heading', 'Quality Commitment',
    'description', 'At Nuteck, quality is not a department; it''s our core philosophy. Every roll of paper that leaves our facility undergoes rigorous testing to ensure it meets international B2B standards. We are committed to sustainable practices and continuous innovation.',
    'bullet_1', 'ISO Certified Production Processes',
    'bullet_2', '100% In-house Quality Inspection',
    'bullet_3', 'Sustainable Material Sourcing',
    'image_path', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
    'image_alt', 'Engineer inspecting material quality in a laboratory'
), 1, 5
FROM pages p
WHERE p.slug = 'about-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'about.quality'
);


INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'blogs.hero', 'Blogs Hero', JSON_OBJECT(
    'badge', 'Insights & Updates',
    'heading', 'Our Blog',
    'description', 'Read the latest updates, product knowledge, and industry insights from Nuteck Paper Products.',
    'background_image_path', '',
    'background_image_alt', 'Blog page hero background',
    'primary_cta_label', 'Explore Products',
    'primary_cta_link', '/product-catalog',
    'secondary_cta_label', 'Contact Us',
    'secondary_cta_link', '/contact-us'
), 1, 1
FROM pages p
WHERE p.slug = 'blogs'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'blogs.hero'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'blogs.listing', 'Blogs Listing', JSON_OBJECT(
    'section_label', 'Latest articles',
    'heading', 'Fresh thinking from the Nuteck team',
    'description', 'Browse product know-how, manufacturing updates, and practical guidance for labels, packaging, and release applications.',
    'featured_label', 'Featured article',
    'read_more_label', 'Read article',
    'published_label', 'Published',
    'empty_heading', 'Blogs coming soon',
    'empty_description', 'Publish your first post from the admin panel to populate this page.'
), 1, 2
FROM pages p
WHERE p.slug = 'blogs'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'blogs.listing'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'contact.intro', 'Contact Intro', JSON_OBJECT(
    'heading', 'Let us start a conversation',
    'description', 'Have questions about our premium paper products? Our team is ready to help.'
), 1, 1
FROM pages p
WHERE p.slug = 'contact-us'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'contact.intro'
);

INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order)
SELECT p.id, 'catalog.hero', 'Catalog Hero', JSON_OBJECT(
    'heading', 'Premium B2B Paper Solutions',
    'description', 'Discover our full range of industrial-grade paper products.'
), 1, 1
FROM pages p
WHERE p.slug = 'product-catalog'
AND NOT EXISTS (
    SELECT 1 FROM page_sections ps WHERE ps.page_id = p.id AND ps.section_key = 'catalog.hero'
);

INSERT INTO product_categories (name, slug, description, sort_order, is_active) VALUES
('Release Papers', 'release-papers', 'Release liner and coated paper solutions', 1, 1),
('Adhesive Papers', 'adhesive-papers', 'Pressure sensitive and adhesive paper products', 2, 1),
('Specialty Foils', 'specialty-foils', 'Decorative and functional foil substrates', 3, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), sort_order = VALUES(sort_order), is_active = VALUES(is_active);

INSERT INTO products (category_id, title, slug, short_description, long_description, specifications_json, features_json, applications_json, sort_order, status, published_at)
SELECT c.id, 'Glassine Release Paper', 'glassine-release-paper',
       'Smooth translucent release paper with stable peel characteristics.',
       'Engineered for precision converting where predictable release values and high web stability are required.',
       JSON_OBJECT('Base paper', '60-120 GSM', 'Coating', 'Silicone', 'Finish', 'Super-calendered'),
       JSON_ARRAY('Consistent release performance', 'Excellent web handling', 'Low lint surface'),
       JSON_ARRAY('Label converting', 'Tape backing', 'Industrial laminates'),
       1, 'published', NOW()
FROM product_categories c
WHERE c.slug = 'release-papers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'glassine-release-paper');

INSERT INTO products (category_id, title, slug, short_description, long_description, specifications_json, features_json, applications_json, sort_order, status, published_at)
SELECT c.id, 'CCK Release Paper', 'cck-release-paper',
       'Clay-coated kraft release liner for industrial adhesive applications.',
       'Durable coated kraft liner designed for high-speed release applications and dimensional stability.',
       JSON_OBJECT('Base paper', '80-140 GSM', 'Coating', 'Silicone on CCK', 'Color', 'Natural'),
       JSON_ARRAY('High tensile strength', 'Excellent stiffness', 'Reliable release range'),
       JSON_ARRAY('Adhesive labels', 'Medical tapes', 'Industrial laminates'),
       2, 'published', NOW()
FROM product_categories c
WHERE c.slug = 'release-papers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'cck-release-paper');

INSERT INTO products (category_id, title, slug, short_description, long_description, specifications_json, features_json, applications_json, sort_order, status, published_at)
SELECT c.id, 'Pre Gummed Paper', 'pre-gummed-paper',
       'Reliable pre-gummed stock for high-volume converting.',
       'Pre-gummed solution designed for stable adhesion and clean processing in bulk operations.',
       JSON_OBJECT('Base paper', '70-110 GSM', 'Adhesive', 'Water-activated', 'Finish', 'Matte/Gloss'),
       JSON_ARRAY('Good tack development', 'Uniform coating', 'Print-friendly surface'),
       JSON_ARRAY('Label stock', 'Packaging components', 'Stationery products'),
       3, 'published', NOW()
FROM product_categories c
WHERE c.slug = 'adhesive-papers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'pre-gummed-paper');

INSERT INTO settings (setting_group, setting_key, setting_value, value_type, is_public) VALUES
('site', 'title', 'Nuteck Paper Products', 'string', 1),
('site', 'logo_path', '/assets/img/nutech_square_logo.png', 'string', 1),
('site', 'social_linkedin', '', 'string', 1),
('site', 'social_facebook', '', 'string', 1),
('site', 'social_instagram', '', 'string', 1),
('site', 'home_hero_image', 'https://images.unsplash.com/photo-1603484477859-abe6a73f9366?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', 'string', 1),
('site', 'home_hero_image_alt', 'Nuteck manufacturing', 'string', 1),
('site', 'contact_email', 'info@nutechpaper.com', 'string', 1),
('site', 'contact_phone', '+91 11 5555 4444', 'string', 1),
('site', 'address', 'Plot No. 45, Okhla Industrial Estate, Phase III, New Delhi - 110020, India', 'string', 1),
('theme', 'primary_color', '#67C6D0', 'string', 1),
('theme', 'primary_hover_color', '#2F8FA1', 'string', 1),
('theme', 'dark_navy_color', '#0F1B2A', 'string', 1),
('theme', 'background_light_color', '#F8FAFC', 'string', 1)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value_type = VALUES(value_type), is_public = VALUES(is_public);

INSERT INTO seo_meta (entity_type, entity_id, meta_title, meta_description, robots)
VALUES ('global', 0, 'Nuteck Paper Products', 'Industrial self-adhesive and release paper manufacturer for B2B applications.', 'index,follow')
ON DUPLICATE KEY UPDATE meta_title = VALUES(meta_title), meta_description = VALUES(meta_description), robots = VALUES(robots);

INSERT INTO navigation_items (menu_key, label, href, sort_order, is_active) VALUES
('primary', 'Home', '/', 1, 1),
('primary', 'Products', '/product-catalog', 2, 1),
('primary', 'About', '/about-us', 3, 1),
('primary', 'Blogs', '/blogs', 4, 1),
('primary', 'Contact', '/contact-us', 5, 1),
('footer', 'Home', '/', 1, 1),
('footer', 'Products', '/product-catalog', 2, 1),
('footer', 'About Us', '/about-us', 3, 1),
('footer', 'Blogs', '/blogs', 4, 1),
('footer', 'Contact', '/contact-us', 5, 1)
ON DUPLICATE KEY UPDATE href = VALUES(href), sort_order = VALUES(sort_order), is_active = VALUES(is_active);


INSERT INTO blogs (title, slug, excerpt, content_html, status, published_at, seo_title, seo_description, robots)
SELECT 'How Release Papers Support Consistent Converting Performance', 'how-release-papers-support-consistent-converting-performance',
       'A quick look at how release papers help manufacturers maintain stable converting and labeling operations.',
       '<p>Release papers are a critical component in high-volume converting operations because they provide a controlled separation layer for pressure-sensitive materials.</p><p>At Nuteck Paper Products, we focus on consistent coating quality, dimensional stability, and dependable release values so manufacturers can reduce waste and improve throughput.</p><h2>Why consistency matters</h2><p>When release values fluctuate, operations can experience downtime, waste, and quality issues. A well-engineered release base helps teams maintain predictable production output.</p><ul><li>Stable release characteristics</li><li>Good web handling</li><li>Improved downstream performance</li></ul>',
       'published', NOW(),
       'How Release Papers Support Consistent Converting Performance | Nuteck',
       'Learn how release papers contribute to stable converting performance and efficient manufacturing workflows.',
       'index,follow'
WHERE NOT EXISTS (
    SELECT 1 FROM blogs WHERE slug = 'how-release-papers-support-consistent-converting-performance'
);
