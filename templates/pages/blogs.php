<?php
declare(strict_types=1);

$sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
$blogs = is_array($blogs ?? null) ? $blogs : [];

$getSectionContent = static function (string $sectionKey, array $defaults = []) use ($sections): array {
    $content = $sections[$sectionKey]['content'] ?? [];
    if (!is_array($content)) {
        $content = [];
    }
    return array_replace($defaults, $content);
};

$isSectionVisible = static function (string $sectionKey) use ($sections): bool {
    return (bool) ($sections[$sectionKey]['is_visible'] ?? true);
};

$resolveAssetUrl = static function (string $value): string {
    $path = trim($value);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $path) === 1) {
        return $path;
    }
    return $path[0] === '/' ? path_url($path) : asset($path);
};

$resolveLink = static function (string $value): string {
    $path = trim($value);
    if ($path === '' || $path === '#') {
        return '#';
    }
    if (preg_match('#^(https?:)?//#i', $path) === 1) {
        return $path;
    }
    return path_url($path[0] === '/' ? $path : '/' . ltrim($path, '/'));
};

$hero = $getSectionContent('blogs.hero', [
    'badge' => 'Insights & Updates',
    'heading' => 'Our Blog',
    'description' => 'Read the latest updates, product knowledge, and industry insights from Nuteck Paper Products.',
    'background_image_path' => '',
    'background_image_alt' => 'Blog page hero background',
    'primary_cta_label' => 'Explore Products',
    'primary_cta_link' => '/product-catalog',
    'secondary_cta_label' => 'Contact Us',
    'secondary_cta_link' => '/contact-us',
]);

$listing = $getSectionContent('blogs.listing', [
    'section_label' => 'Latest articles',
    'heading' => 'Fresh thinking from the Nuteck team',
    'description' => 'Browse product know-how, manufacturing updates, and practical guidance for labels, packaging, and release applications.',
    'read_more_label' => 'Read article',
    'published_label' => 'Published',
    'empty_heading' => 'Blogs coming soon',
    'empty_description' => 'Publish your first post from the admin panel to populate this page.',
]);

$heroBackground = $resolveAssetUrl((string) ($hero['background_image_path'] ?? ''));
?>
<div class="bg-slate-50">
    <?php if ($isSectionVisible('blogs.hero')): ?>
    <section class="relative overflow-hidden bg-dark-navy">
        <?php if ($heroBackground !== ''): ?>
        <div class="absolute inset-0 bg-cover bg-center opacity-25" style="background-image: url('<?= e($heroBackground) ?>')" aria-label="<?= e((string) ($hero['background_image_alt'] ?? 'Blog page hero background')) ?>"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(103,198,208,0.18),transparent_30%),linear-gradient(135deg,rgba(15,27,42,0.98),rgba(15,27,42,0.88))]"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
            <span class="inline-flex px-3 py-1 rounded-full bg-primary/15 text-primary text-xs font-bold uppercase tracking-[0.2em] mb-5"><?= e((string) ($hero['badge'] ?? 'Insights & Updates')) ?></span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-[1.05] mb-5"><?= e((string) ($hero['heading'] ?? 'Our Blog')) ?></h1>
            <p class="mx-auto max-w-3xl text-lg leading-8 text-slate-300 mb-8"><?= e((string) ($hero['description'] ?? 'Read the latest updates, product knowledge, and industry insights from Nuteck Paper Products.')) ?></p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <?php if (trim((string) ($hero['primary_cta_label'] ?? '')) !== ''): ?>
                <a class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-sm font-bold text-dark-navy transition-colors hover:bg-primary-hover" href="<?= e($resolveLink((string) ($hero['primary_cta_link'] ?? '/product-catalog'))) ?>"><?= e((string) ($hero['primary_cta_label'] ?? 'Explore Products')) ?></a>
                <?php endif; ?>
                <?php if (trim((string) ($hero['secondary_cta_label'] ?? '')) !== ''): ?>
                <a class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/10" href="<?= e($resolveLink((string) ($hero['secondary_cta_link'] ?? '/contact-us'))) ?>"><?= e((string) ($hero['secondary_cta_label'] ?? 'Contact Us')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-14 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-10 lg:mb-12">
                <span class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-primary mb-4"><?= e((string) ($listing['section_label'] ?? 'Latest articles')) ?></span>
                <h2 class="text-3xl lg:text-4xl font-black text-dark-navy mb-4"><?= e((string) ($listing['heading'] ?? 'Fresh thinking from the Nuteck team')) ?></h2>
                <p class="text-base lg:text-lg leading-8 text-slate-600"><?= e((string) ($listing['description'] ?? 'Browse product know-how, manufacturing updates, and practical guidance for labels, packaging, and release applications.')) ?></p>
            </div>

            <?php if ($blogs !== []): ?>
            <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($blogs as $blog): ?>
                <article class="group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <a class="block" href="<?= e(path_url('/blogs/' . (string) ($blog['slug'] ?? ''))) ?>">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                            <?php if (!empty($blog['featured_image_path'])): ?>
                            <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="<?= e((string) ($blog['featured_image_alt'] ?? $blog['title'] ?? 'Blog image')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <?php else: ?>
                            <div class="h-full w-full bg-[linear-gradient(135deg,rgba(103,198,208,0.18),rgba(15,27,42,0.08))]"></div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 mb-3">
                            <?= e((string) ($listing['published_label'] ?? 'Published')) ?> ·
                            <?= e(!empty($blog['published_at']) ? date('F j, Y', strtotime((string) $blog['published_at'])) : date('F j, Y', strtotime((string) ($blog['created_at'] ?? 'now')))) ?>
                        </p>
                        <h3 class="text-2xl font-black leading-tight text-dark-navy mb-3 line-clamp-2">
                            <a href="<?= e(path_url('/blogs/' . (string) ($blog['slug'] ?? ''))) ?>" class="transition-colors hover:text-primary"><?= e((string) ($blog['title'] ?? '')) ?></a>
                        </h3>
                        <p class="text-slate-600 leading-7 line-clamp-4 mb-6"><?= e((string) ($blog['excerpt'] ?? '')) ?></p>
                        <a href="<?= e(path_url('/blogs/' . (string) ($blog['slug'] ?? ''))) ?>" class="mt-auto inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                            <?= e((string) ($listing['read_more_label'] ?? 'Read article')) ?>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <article class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto mb-5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-3xl">edit_square</span>
                </div>
                <h2 class="text-2xl font-black text-dark-navy mb-3"><?= e((string) ($listing['empty_heading'] ?? 'Blogs coming soon')) ?></h2>
                <p class="mx-auto max-w-2xl text-slate-600"><?= e((string) ($listing['empty_description'] ?? 'Publish your first post from the admin panel to populate this page.')) ?></p>
            </article>
            <?php endif; ?>
        </div>
    </section>
</div>
