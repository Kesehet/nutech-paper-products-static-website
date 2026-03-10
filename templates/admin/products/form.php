<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy"><?= e((string) ($submitLabel ?? 'Save Product')) ?></h2>
            <p class="text-sm text-slate-600 mt-1">Maintain product details used on catalog and product detail pages.</p>
        </div>
        <a href="<?= e(path_url('/admin/products')) ?>" class="text-sm font-semibold text-primary hover:underline">Back to Products</a>
    </div>

    <form action="<?= e(path_url((string) $formAction)) ?>" method="post" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Title *</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="title" value="<?= e((string) ($product['title'] ?? '')) ?>" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Slug</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="slug" value="<?= e((string) ($product['slug'] ?? '')) ?>" placeholder="auto-generated-if-empty">
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Category</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) ($category['id'] ?? '')) ?>" <?= ((string) ($product['category_id'] ?? '') === (string) ($category['id'] ?? '')) ? 'selected' : '' ?>>
                        <?= e((string) ($category['name'] ?? '')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Sort Order</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="number" name="sort_order" value="<?= e((string) ($product['sort_order'] ?? 0)) ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Status</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="status">
                    <?php $status = (string) ($product['status'] ?? 'draft'); ?>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Short Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="short_description"><?= e((string) ($product['short_description'] ?? '')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Long Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="6" name="long_description"><?= e((string) ($product['long_description'] ?? '')) ?></textarea>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Specifications JSON</label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7" name="specifications_json"><?= e((string) ($product['specifications_json'] ?? '{}')) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Features JSON</label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7" name="features_json"><?= e((string) ($product['features_json'] ?? '[]')) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Applications JSON</label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7" name="applications_json"><?= e((string) ($product['applications_json'] ?? '[]')) ?></textarea>
            </div>
        </div>

        <div>
            <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit"><?= e((string) $submitLabel) ?></button>
        </div>
    </form>
</section>

