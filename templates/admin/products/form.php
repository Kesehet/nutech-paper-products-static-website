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
                <a href="<?= e(path_url('/admin/categories')) ?>" class="inline-block mt-2 text-xs text-primary font-semibold hover:underline">Manage Categories</a>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Featured Image</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="featured_image_id">
                    <option value="">No featured image</option>
                    <?php foreach ($mediaLibrary as $media): ?>
                    <option value="<?= e((string) ($media['id'] ?? '')) ?>" <?= ((string) ($product['featured_image_id'] ?? '') === (string) ($media['id'] ?? '')) ? 'selected' : '' ?>>
                        #<?= e((string) ($media['id'] ?? '')) ?> - <?= e((string) ($media['original_name'] ?? '')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-4">
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

        <div class="rounded-xl border border-slate-200 p-4">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Gallery Images</h3>
            <p class="text-xs text-slate-500 mb-4">Select reusable media, set display order, and choose a primary gallery image.</p>
            <div class="space-y-3 max-h-96 overflow-auto pr-1">
                <?php foreach ($mediaLibrary as $media): ?>
                    <?php
                    $mediaId = (int) ($media['id'] ?? 0);
                    $selection = $gallerySelections[$mediaId] ?? null;
                    $isChecked = is_array($selection);
                    $isPrimary = (bool) (($selection['is_primary'] ?? false));
                    $sortOrder = (int) ($selection['sort_order'] ?? 0);
                    $altText = (string) ($selection['alt_text'] ?? '');
                    ?>
                    <div class="grid md:grid-cols-12 gap-3 border border-slate-100 rounded-lg p-3 items-center">
                        <div class="md:col-span-4 flex items-center gap-3">
                            <input type="checkbox" name="gallery_media_id[]" value="<?= e((string) $mediaId) ?>" <?= $isChecked ? 'checked' : '' ?>>
                            <?php if (str_starts_with((string) ($media['mime_type'] ?? ''), 'image/')): ?>
                                <img src="<?= e(path_url((string) ($media['storage_path'] ?? ''))) ?>" alt="" class="w-12 h-12 object-cover rounded border border-slate-200">
                            <?php else: ?>
                                <span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs">File</span>
                            <?php endif; ?>
                            <div>
                                <p class="text-xs text-slate-500">#<?= e((string) $mediaId) ?></p>
                                <p class="text-sm font-semibold text-slate-800"><?= e((string) ($media['original_name'] ?? '')) ?></p>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Alt Text</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="gallery_alt_text[<?= e((string) $mediaId) ?>]" value="<?= e($altText) ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="number" name="gallery_sort_order[<?= e((string) $mediaId) ?>]" value="<?= e((string) ($sortOrder > 0 ? $sortOrder : 0)) ?>">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Primary</label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="gallery_primary_media_id" value="<?= e((string) $mediaId) ?>" <?= $isPrimary ? 'checked' : '' ?>>
                                Primary image
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
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
