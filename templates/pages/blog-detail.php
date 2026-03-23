<?php
declare(strict_types=1);

$publishedDate = !empty($blog['published_at'])
    ? date('F j, Y', strtotime((string) $blog['published_at']))
    : date('F j, Y', strtotime((string) ($blog['created_at'] ?? 'now')));
?>
<article class="bg-slate-50 py-10 lg:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-8 flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a class="transition-colors hover:text-primary" href="<?= e(path_url('/')) ?>">Home</a>
            <span>/</span>
            <a class="transition-colors hover:text-primary" href="<?= e(path_url('/blogs')) ?>">Blogs</a>
            <span>/</span>
            <span class="text-slate-700"><?= e((string) ($blog['title'] ?? 'Blog')) ?></span>
        </nav>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <header class="relative overflow-hidden bg-dark-navy px-6 py-10 sm:px-8 lg:px-12 lg:py-14">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(103,198,208,0.18),transparent_35%),linear-gradient(135deg,rgba(15,27,42,1),rgba(15,27,42,0.88))]"></div>
                <div class="relative z-10 max-w-4xl">
                    <p class="mb-4 inline-flex rounded-full bg-primary/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-primary"><?= e($publishedDate) ?></p>
                    <h1 class="text-4xl lg:text-5xl font-black leading-tight text-white mb-5"><?= e((string) ($blog['title'] ?? '')) ?></h1>
                    <?php if (!empty($blog['excerpt'])): ?>
                    <p class="max-w-3xl text-lg leading-8 text-slate-300"><?= e((string) $blog['excerpt']) ?></p>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (!empty($blog['featured_image_path'])): ?>
            <div class="border-b border-slate-200 bg-slate-100 p-4 sm:p-6 lg:p-8">
                <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="<?= e((string) ($blog['featured_image_alt'] ?? $blog['title'] ?? 'Blog image')) ?>" class="max-h-[36rem] w-full object-cover">
                </div>
            </div>
            <?php endif; ?>

            <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div class="border-b border-slate-200 p-6 sm:p-8 lg:border-b-0 lg:border-r lg:p-12">
                    <div class="prose prose-slate max-w-none prose-headings:text-dark-navy prose-headings:font-black prose-a:text-primary prose-img:rounded-2xl prose-img:border prose-img:border-slate-200 prose-blockquote:border-primary/40 prose-blockquote:text-slate-600 prose-p:leading-8">
                        <?= (string) ($blog['content_html'] ?? '') ?>
                    </div>
                </div>
                <aside class="bg-slate-50/70 p-6 sm:p-8 lg:p-10">
                    <div class="sticky top-28 space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 mb-2">Published</p>
                            <p class="text-lg font-black text-dark-navy"><?= e($publishedDate) ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 mb-3">Continue exploring</p>
                            <div class="space-y-3">
                                <a class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-dark-navy transition-colors hover:border-primary/50 hover:text-primary" href="<?= e(path_url('/blogs')) ?>">
                                    Back to blog index
                                    <span aria-hidden="true">→</span>
                                </a>
                                <a class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-dark-navy transition-colors hover:border-primary/50 hover:text-primary" href="<?= e(path_url('/contact-us')) ?>">
                                    Talk to our team
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</article>
