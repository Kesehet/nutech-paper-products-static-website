<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy">Products</h2>
            <p class="text-sm text-slate-600 mt-1">Manage product catalog entries and publish status.</p>
        </div>
        <a href="<?= e(path_url('/admin/products/create')) ?>" class="inline-flex items-center px-4 py-2 bg-primary text-dark-navy rounded-lg text-sm font-bold">Add Product</a>
    </div>

    <form class="mb-6" method="get" action="<?= e(path_url('/admin/products')) ?>">
        <input class="w-full md:w-96 rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="search" name="q" placeholder="Search by title or slug..." value="<?= e((string) ($search ?? '')) ?>">
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="py-3 pr-4">Title</th>
                    <th class="py-3 pr-4">Slug</th>
                    <th class="py-3 pr-4">Category</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3 pr-4">Sort</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr class="border-b border-slate-100">
                    <td class="py-3 pr-4 font-semibold text-slate-800"><?= e((string) ($product['title'] ?? '')) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($product['slug'] ?? '')) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($product['category_name'] ?? '-')) ?></td>
                    <td class="py-3 pr-4">
                        <?php $status = (string) ($product['status'] ?? 'draft'); ?>
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?= $status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($status === 'archived' ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-700') ?>">
                            <?= e(ucfirst($status)) ?>
                        </span>
                    </td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($product['sort_order'] ?? 0)) ?></td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <a class="text-primary font-semibold hover:underline" href="<?= e(path_url('/admin/products/' . (string) ($product['id'] ?? 0) . '/edit')) ?>">Edit</a>
                            <form method="post" action="<?= e(path_url('/admin/products/' . (string) ($product['id'] ?? 0) . '/delete')) ?>" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                                <button class="text-red-600 font-semibold hover:underline" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

