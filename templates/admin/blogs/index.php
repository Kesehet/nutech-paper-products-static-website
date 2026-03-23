<?php
declare(strict_types=1);
?>
<section class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-black text-dark-navy mb-2">Blogs</h2>
                <p class="text-sm text-slate-600">Create, publish, and optimize blog content for the frontend blog experience.</p>
            </div>
            <a href="<?= e(path_url('/admin/blogs/create')) ?>" class="inline-flex items-center justify-center px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors">New Blog</a>
        </div>

        <form class="mb-4" method="get" action="<?= e(path_url('/admin/blogs')) ?>">
            <input class="w-full md:w-96 rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="search" name="q" placeholder="Search blogs by title or slug..." value="<?= e((string) ($search ?? '')) ?>">
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-200">
                        <th class="py-3 pr-4">Post</th>
                        <th class="py-3 pr-4">Slug</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Published</th>
                        <th class="py-3 pr-4">Updated</th>
                        <th class="py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3 min-w-[240px]">
                                <?php if (!empty($blog['featured_image_path'])): ?>
                                    <img src="<?= e(path_url((string) $blog['featured_image_path'])) ?>" alt="" class="w-14 h-14 rounded-lg object-cover border border-slate-200">
                                <?php else: ?>
                                    <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200"></div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-semibold text-slate-800"><?= e((string) ($blog['title'] ?? '')) ?></p>
                                    <p class="text-xs text-slate-500 line-clamp-2"><?= e((string) ($blog['excerpt'] ?? '')) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($blog['slug'] ?? '')) ?></td>
                        <td class="py-3 pr-4">
                            <?php $status = (string) ($blog['status'] ?? 'draft'); ?>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($status === 'archived' ? 'bg-slate-200 text-slate-600' : 'bg-amber-100 text-amber-700') ?>">
                                <?= e(ucfirst($status)) ?>
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($blog['published_at'] ?? '-')) ?></td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($blog['updated_at'] ?? '')) ?></td>
                        <td class="py-3">
                            <div class="flex flex-col items-start gap-2">
                                <a href="<?= e(path_url('/admin/blogs/' . (string) ($blog['id'] ?? 0) . '/edit')) ?>" class="text-primary font-semibold hover:underline">Edit</a>
                                <form method="post" action="<?= e(path_url('/admin/blogs/' . (string) ($blog['id'] ?? 0) . '/delete')) ?>" onsubmit="return confirm('Delete this blog post?');">
                                    <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                                    <button class="text-red-600 font-semibold hover:underline" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($blogs === []): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No blog posts found yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        $basePath = '/admin/blogs';
        $query = ['q' => (string) ($search ?? '')];
        $pageKey = 'page';
        require BASE_PATH . '/templates/admin/partials/pagination.php';
        ?>
    </div>
</section>
