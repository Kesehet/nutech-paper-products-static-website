<?php
declare(strict_types=1);

$sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
$products = is_array($products ?? null) ? $products : [];
$categories = is_array($categories ?? null) ? $categories : [];
$currentCategory = trim((string) ($currentCategory ?? ''));
$search = trim((string) ($search ?? ''));

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

$hero = $getSectionContent('catalog.hero', [
    'badge' => 'Industrial Excellence',
    'heading' => 'Premium B2B Paper Solutions',
    'description' => 'Specializing in high-performance release papers, specialty foils, and adhesive stocks for global manufacturing.',
    'image_path' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&w=1800&q=80',
    'image_alt' => 'Industrial paper manufacturing facility with rolls of paper',
    'primary_cta_label' => 'Download Brochure',
    'primary_cta_link' => '/contact-us',
    'secondary_cta_label' => 'Inquire Now',
    'secondary_cta_link' => '/contact-us',
]);

$listing = $getSectionContent('catalog.listing', [
    'heading' => 'Product Catalog',
    'description' => 'Showing all industrial grade materials',
    'all_label' => 'All Products',
    'search_placeholder' => 'Search catalog...',
    'stock_label' => 'In Stock',
]);

$customCta = $getSectionContent('catalog.custom_cta', [
    'heading' => 'Custom Requirement?',
    'description' => "Can't find what you need? We offer custom coating and sizing solutions for unique industrial needs.",
    'button_label' => 'Contact Sales',
    'button_link' => '/contact-us',
]);

$heroImage = $resolveAssetUrl((string) ($hero['image_path'] ?? ''));
?>
<?php if ($isSectionVisible('catalog.hero')): ?>
<section class="relative overflow-hidden rounded-xl mb-12 bg-slate-900 aspect-[21/9] flex items-center">
    <div class="absolute inset-0 opacity-40 bg-cover bg-center" style="background-image: url('<?= e($heroImage) ?>')" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent"></div>
    <div class="relative z-10 px-8 lg:px-16 max-w-2xl">
        <span class="inline-block px-3 py-1 rounded-full bg-primary/20 text-primary text-xs font-bold uppercase tracking-wider mb-4"><?= e((string) ($hero['badge'] ?? '')) ?></span>
        <h1 class="text-4xl lg:text-6xl font-black text-white leading-tight mb-4"><?= e((string) ($hero['heading'] ?? '')) ?></h1>
        <p class="text-slate-300 text-lg mb-8"><?= e((string) ($hero['description'] ?? '')) ?></p>
        <div class="flex flex-wrap gap-4">
            <?php if (trim((string) ($hero['primary_cta_label'] ?? '')) !== ''): ?>
            <a class="bg-primary hover:bg-primary-hover text-slate-900 font-bold px-6 py-3 rounded-lg transition-all" href="<?= e($resolveLink((string) ($hero['primary_cta_link'] ?? '#'))) ?>"><?= e((string) ($hero['primary_cta_label'] ?? '')) ?></a>
            <?php endif; ?>
            <?php if (trim((string) ($hero['secondary_cta_label'] ?? '')) !== ''): ?>
            <a class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-bold px-6 py-3 rounded-lg border border-white/20 transition-all" href="<?= e($resolveLink((string) ($hero['secondary_cta_link'] ?? '#'))) ?>"><?= e((string) ($hero['secondary_cta_label'] ?? '')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isSectionVisible('catalog.listing')): ?>
<section class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900"><?= e((string) ($listing['heading'] ?? 'Product Catalog')) ?></h2>
            <p class="text-slate-500"><?= e((string) ($listing['description'] ?? '')) ?></p>
        </div>
        <form class="w-full md:w-auto" method="get" action="<?= e(path_url('/product-catalog')) ?>">
            <?php if ($currentCategory !== ''): ?>
            <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
            <?php endif; ?>
            <div class="relative w-full md:w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
                <input class="w-full pl-10 pr-4 py-2 bg-slate-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/50 transition-all outline-none" type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e((string) ($listing['search_placeholder'] ?? 'Search catalog...')) ?>">
            </div>
        </form>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 mb-8">
        <a class="whitespace-nowrap px-4 py-2 rounded-xl font-semibold text-sm <?= $currentCategory === '' ? 'bg-primary text-slate-900' : 'bg-slate-200 text-slate-600 hover:bg-slate-300 transition-colors' ?>" href="<?= e(query_url('/product-catalog', ['q' => $search])) ?>">
            <?= e((string) ($listing['all_label'] ?? 'All Products')) ?>
        </a>
        <?php foreach ($categories as $category): ?>
            <?php
            $slug = (string) ($category['slug'] ?? '');
            $label = (string) ($category['name'] ?? $slug);
            if ($slug === '') {
                continue;
            }
            $isActive = $currentCategory === $slug;
            ?>
            <a class="whitespace-nowrap px-4 py-2 rounded-xl font-medium text-sm <?= $isActive ? 'bg-primary text-slate-900' : 'bg-slate-200 text-slate-600 hover:bg-slate-300 transition-colors' ?>" href="<?= e(query_url('/product-catalog', ['category' => $slug, 'q' => $search])) ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
        <article class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="aspect-video relative overflow-hidden">
                <?php if (!empty($product['featured_image_path'])): ?>
                    <img src="<?= e(path_url((string) $product['featured_image_path'])) ?>" alt="<?= e((string) ($product['title'] ?? 'Product')) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <?php else: ?>
                    <div class="w-full h-full bg-slate-100"></div>
                <?php endif; ?>
                <?php if (trim((string) ($listing['stock_label'] ?? '')) !== ''): ?>
                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2 py-1 rounded text-[10px] font-bold uppercase text-slate-600"><?= e((string) ($listing['stock_label'] ?? '')) ?></div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <h3 class="text-lg font-bold text-slate-900 mb-2"><?= e((string) ($product['title'] ?? '')) ?></h3>
                <p class="text-sm text-slate-500 line-clamp-2 mb-4"><?= e((string) ($product['short_description'] ?? '')) ?></p>
                <div class="space-y-3">
                    <a class="w-full bg-primary hover:bg-primary-hover text-slate-900 font-bold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2" href="<?= e(query_url('/contact-us', ['product' => (string) ($product['slug'] ?? '')])) ?>">
                        <span class="material-symbols-outlined text-lg">request_quote</span>
                        Get Best Price
                    </a>
                    <a class="block text-center text-sm font-semibold text-primary hover:underline" href="<?= e(path_url('/product/' . (string) ($product['slug'] ?? ''))) ?>">View Details</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if ($isSectionVisible('catalog.custom_cta')): ?>
        <article class="bg-primary/10 border-2 border-dashed border-primary/30 rounded-xl flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-3xl text-primary">add_circle</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2"><?= e((string) ($customCta['heading'] ?? '')) ?></h3>
            <p class="text-sm text-slate-500 mb-6"><?= e((string) ($customCta['description'] ?? '')) ?></p>
            <a class="bg-primary text-slate-900 font-bold px-6 py-2 rounded-lg text-sm transition-transform hover:scale-105 active:scale-95" href="<?= e($resolveLink((string) ($customCta['button_link'] ?? '/contact-us'))) ?>"><?= e((string) ($customCta['button_label'] ?? 'Contact Sales')) ?></a>
        </article>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
