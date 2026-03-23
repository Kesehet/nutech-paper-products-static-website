<?php
declare(strict_types=1);
?>
<article class="py-10 lg:py-14 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
            <a class="hover:text-primary" href="<?= e(path_url('/')) ?>">Home</a>
            <span>/</span>
            <a class="hover:text-primary" href="<?= e(path_url('/blogs')) ?>">Blogs</a>
            <span>/</span>
            <span class="text-slate-700"><?= e((string) ($blog['title'] ?? 'Blog')) ?></span>
        </nav>

        <header class="mb-10">
            <p class="text-xs uppercase tracking-wider text-primary font-bold mb-4"><?= e(!empty($blog['published_at']) ? date('F j, Y', strtotime((string) $blog['published_at'])) : date('F j, Y', strtotime((string) ($blog['created_at'] ?? 'now')))) ?></p>
            <h1 class="text-4xl lg:text-5xl font-black text-dark-navy leading-tight mb-5"><?= e((string) ($blog['title'] ?? '')) ?></h1>
            <?php if (!empty($blog['excerpt'])): ?>
            <p class="text-lg text-slate-600 max-w-3xl"><?= e((string) $blog['excerpt']) ?></p>
            <?php endif; ?>
        </header>

        <?php if (!empty($blog['featured_image_path'])): ?>
        <div class="mb-10 rounded-3xl overflow-hidden border border-slate-200 bg-white shadow-sm">
            <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="<?= e((string) ($blog['featured_image_alt'] ?? $blog['title'] ?? 'Blog image')) ?>" class="w-full h-auto max-h-[32rem] object-cover">
        </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-10 shadow-sm prose prose-slate max-w-none prose-headings:text-dark-navy prose-a:text-primary prose-img:rounded-2xl prose-img:border prose-img:border-slate-200 prose-blockquote:border-primary/40 prose-blockquote:text-slate-600">
            <?= (string) ($blog['content_html'] ?? '') ?>
        </div>
    </div>
</article>
