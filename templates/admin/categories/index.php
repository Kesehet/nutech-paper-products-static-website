<?php
declare(strict_types=1);
?>
<section class="space-y-6">
    <form action="<?= e(path_url('/admin/categories/store')) ?>" method="post" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <h2 class="text-2xl font-black text-dark-navy mb-2">Create Category</h2>
        <p class="text-sm text-slate-600 mb-5">Organize products for catalog filters and navigation.</p>
        <div class="grid md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Name *</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="name" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Slug</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="slug" placeholder="auto-from-name">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Description</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="description">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-2">Sort</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="number" name="sort_order" value="0">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Active</label>
                    <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="is_active">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button class="px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Create Category</button>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <h3 class="text-xl font-black text-dark-navy mb-4">Categories</h3>
        <div class="space-y-4">
            <?php foreach ($categories as $row): ?>
                <div class="rounded-xl border border-slate-200 p-4">
                    <form action="<?= e(path_url('/admin/categories/' . (string) ($row['id'] ?? 0) . '/update')) ?>" method="post" class="grid md:grid-cols-8 gap-3 items-end">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Name</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="name" value="<?= e((string) ($row['name'] ?? '')) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Slug</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="slug" value="<?= e((string) ($row['slug'] ?? '')) ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Description</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="description" value="<?= e((string) ($row['description'] ?? '')) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="number" name="sort_order" value="<?= e((string) ($row['sort_order'] ?? 0)) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Active</label>
                            <select class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="is_active">
                                <option value="1" <?= ((int) ($row['is_active'] ?? 0) === 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ((int) ($row['is_active'] ?? 0) === 0) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="text-xs text-slate-500">
                            Products: <span class="font-semibold text-slate-700"><?= e((string) ($row['product_count'] ?? 0)) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="px-4 py-2 bg-primary text-dark-navy rounded-lg text-sm font-semibold" type="submit">Save</button>
                        </div>
                    </form>
                    <form action="<?= e(path_url('/admin/categories/' . (string) ($row['id'] ?? 0) . '/delete')) ?>" method="post" class="mt-2" onsubmit="return confirm('Delete this category?');">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                        <button class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold" type="submit">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

