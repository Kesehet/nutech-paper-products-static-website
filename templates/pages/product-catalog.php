<?php
declare(strict_types=1);

$hero = $page['sections']['catalog.hero']['content'] ?? [];
?>
<section class="relative py-20 bg-dark-navy text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <span class="inline-block px-3 py-1 rounded-full bg-primary/20 text-primary text-xs font-bold uppercase tracking-wider mb-4">Industrial Excellence</span>
        <h1 class="text-4xl lg:text-6xl font-black leading-tight mb-4"><?= e((string) ($hero['heading'] ?? 'Premium B2B Paper Solutions')) ?></h1>
        <p class="text-lg text-slate-300 max-w-2xl"><?= e((string) ($hero['description'] ?? 'Browse our range of premium self-adhesive and release paper solutions.')) ?></p>
    </div>
    <div class="absolute inset-0 bg-gradient-to-r from-dark-navy to-dark-navy/40"></div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <h2 class="text-3xl font-bold text-dark-navy">Product Catalog</h2>
            <a class="bg-primary text-dark-navy font-bold px-6 py-3 rounded-lg transition-all hover:scale-[1.02]" href="<?= e(path_url('/contact-us')) ?>">Contact Sales</a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <article class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                <div class="h-44 bg-slate-100"></div>
                <div class="p-6">
                    <p class="text-xs uppercase tracking-wider text-primary font-bold mb-2"><?= e((string) ($product['category_name'] ?? 'Product')) ?></p>
                    <h3 class="text-xl font-bold text-slate-900 mb-2"><?= e((string) ($product['title'] ?? '')) ?></h3>
                    <p class="text-sm text-slate-600 mb-5"><?= e((string) ($product['short_description'] ?? '')) ?></p>
                    <a class="text-sm font-semibold text-primary hover:underline" href="<?= e(path_url('/product/' . (string) ($product['slug'] ?? ''))) ?>">View Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
