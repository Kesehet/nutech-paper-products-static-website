<?php
declare(strict_types=1);

$sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
$hero = $sections['blogs.hero']['content'] ?? [];
if (!is_array($hero)) {
    $hero = [];
}
$blogs = is_array($blogs ?? null) ? $blogs : [];
?>
<section class="bg-dark-navy py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="inline-flex px-3 py-1 rounded-full bg-primary/20 text-primary text-xs font-bold uppercase tracking-wider mb-4"><?= e((string) ($hero['badge'] ?? 'Insights & Updates')) ?></span>
        <h1 class="text-4xl sm:text-5xl font-black text-white leading-tight mb-4"><?= e((string) ($hero['heading'] ?? 'Our Blog')) ?></h1>
        <p class="text-slate-300 max-w-3xl text-lg"><?= e((string) ($hero['description'] ?? 'Read the latest updates, product knowledge, and industry insights from Nuteck Paper Products.')) ?></p>
    </div>
</section>

<section class="py-16 lg:py-20 bg-slate-50 min-h-[40vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
            <?php foreach ($blogs as $blog): ?>
            <article class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                <?php if (!empty($blog['featured_image_path'])): ?>
                    <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="<?= e((string) ($blog['featured_image_alt'] ?? $blog['title'] ?? 'Blog image')) ?>" class="h-56 w-full object-cover">
                <?php else: ?>
                    <div class="h-56 w-full bg-gradient-to-br from-primary/20 to-slate-100"></div>
                <?php endif; ?>
                <div class="p-6 flex-1 flex flex-col">
                    <p class="text-xs uppercase tracking-wider text-slate-500 mb-3"><?= e(!empty($blog['published_at']) ? date('F j, Y', strtotime((string) $blog['published_at'])) : date('F j, Y', strtotime((string) ($blog['created_at'] ?? 'now')))) ?></p>
                    <h2 class="text-2xl font-bold text-dark-navy mb-3 line-clamp-2"><?= e((string) ($blog['title'] ?? '')) ?></h2>
                    <p class="text-slate-600 mb-6 line-clamp-3"><?= e((string) ($blog['excerpt'] ?? '')) ?></p>
                    <a href="<?= e(path_url('/blogs/' . (string) ($blog['slug'] ?? ''))) ?>" class="mt-auto inline-flex items-center gap-2 text-primary font-semibold hover:underline">
                        Read More
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>

            <?php if ($blogs === []): ?>
            <article class="md:col-span-2 xl:col-span-3 bg-white rounded-2xl border border-slate-200 p-10 text-center">
                <h2 class="text-2xl font-bold text-dark-navy mb-3">Blogs coming soon</h2>
                <p class="text-slate-600">Publish your first post from the admin panel to populate this page.</p>
            </article>
            <?php endif; ?>
        </div>
    </div>
</section>
