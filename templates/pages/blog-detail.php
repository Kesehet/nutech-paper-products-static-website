<?php
declare(strict_types=1);

$publishedDate = !empty($blog['published_at'])
    ? date('F j, Y', strtotime((string) $blog['published_at']))
    : date('F j, Y', strtotime((string) ($blog['created_at'] ?? 'now')));
$recommendedBlogs = is_array($recommendedBlogs ?? null) ? $recommendedBlogs : [];
?>
<article class="bg-[#f7f7f5] py-10 lg:py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-8 flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a class="transition-colors hover:text-primary" href="<?= e(path_url('/')) ?>">Home</a>
            <span>/</span>
            <a class="transition-colors hover:text-primary" href="<?= e(path_url('/blogs')) ?>">Blogs</a>
            <span>/</span>
            <span class="text-slate-700"><?= e((string) ($blog['title'] ?? 'Blog')) ?></span>
        </nav>

        <header class="mx-auto max-w-3xl mb-10 lg:mb-12">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary mb-4"><?= e($publishedDate) ?></p>
            <h1 class="text-4xl sm:text-5xl font-black leading-tight text-slate-950 mb-5"><?= e((string) ($blog['title'] ?? '')) ?></h1>
            <?php if (!empty($blog['excerpt'])): ?>
            <p class="text-lg leading-8 text-slate-600"><?= e((string) $blog['excerpt']) ?></p>
            <?php endif; ?>
        </header>

        <?php if (!empty($blog['featured_image_path'])): ?>
        <div class="mx-auto max-w-4xl mb-10 lg:mb-12">
            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="<?= e((string) ($blog['featured_image_alt'] ?? $blog['title'] ?? 'Blog image')) ?>" class="w-full max-h-[26rem] object-cover">
            </div>
        </div>
        <?php endif; ?>

        <div class="mx-auto max-w-3xl rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-sm sm:px-8 lg:px-12 lg:py-12">
            <div class="mb-8 flex flex-wrap items-center gap-3 border-b border-slate-200 pb-6 text-sm text-slate-500">
                <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 font-semibold text-slate-700">
                    <span class="material-symbols-outlined text-base text-primary">calendar_month</span>
                    <?= e($publishedDate) ?>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">
                    <span class="material-symbols-outlined text-base text-primary">auto_stories</span>
                    Nuteck Insights
                </span>
            </div>
            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-950 prose-a:text-primary prose-img:rounded-2xl prose-img:border prose-img:border-slate-200 prose-blockquote:border-primary/40 prose-blockquote:text-slate-600 prose-p:leading-8">
                <?= (string) ($blog['content_html'] ?? '') ?>
            </div>
        </div>

        <?php if ($recommendedBlogs !== []): ?>
        <section class="mx-auto max-w-6xl mt-14 lg:mt-20">
            <div class="max-w-3xl mb-8 lg:mb-10">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary mb-3">Recommended articles</p>
                <h2 class="text-3xl font-black text-slate-950 mb-3">More reading from our blog</h2>
                <p class="text-slate-600 leading-7">Explore more packaging, label, and manufacturing insights from the Nuteck team.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($recommendedBlogs as $recommended): ?>
                <article class="group flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <a class="block" href="<?= e(path_url('/blogs/' . (string) ($recommended['slug'] ?? ''))) ?>">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                            <?php if (!empty($recommended['featured_image_path'])): ?>
                            <img src="<?= e(path_url((string) $recommended['featured_image_path'])) ?>" alt="<?= e((string) ($recommended['featured_image_alt'] ?? $recommended['title'] ?? 'Recommended blog image')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <?php else: ?>
                            <div class="h-full w-full bg-[linear-gradient(135deg,rgba(103,198,208,0.18),rgba(15,27,42,0.08))]"></div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 mb-3">
                            <?= e(!empty($recommended['published_at']) ? date('F j, Y', strtotime((string) $recommended['published_at'])) : date('F j, Y', strtotime((string) ($recommended['created_at'] ?? 'now')))) ?>
                        </p>
                        <h3 class="text-2xl font-black leading-tight text-slate-950 mb-3 line-clamp-2">
                            <a class="transition-colors hover:text-primary" href="<?= e(path_url('/blogs/' . (string) ($recommended['slug'] ?? ''))) ?>"><?= e((string) ($recommended['title'] ?? '')) ?></a>
                        </h3>
                        <p class="text-slate-600 leading-7 line-clamp-4 mb-6"><?= e((string) ($recommended['excerpt'] ?? '')) ?></p>
                        <a class="mt-auto inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline" href="<?= e(path_url('/blogs/' . (string) ($recommended['slug'] ?? ''))) ?>">
                            Read article
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</article>
