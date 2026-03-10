<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy">Edit <?= e(ucfirst((string) $entityType)) ?> SEO</h2>
            <p class="text-sm text-slate-600 mt-1"><?= e((string) ($entity['title'] ?? '')) ?> (ID: <?= e((string) ($entity['id'] ?? '')) ?>)</p>
        </div>
        <a href="<?= e(path_url('/admin/seo')) ?>" class="text-sm font-semibold text-primary hover:underline">Back to SEO</a>
    </div>

    <form action="<?= e(path_url('/admin/seo/save')) ?>" method="post" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <input type="hidden" name="entity_type" value="<?= e((string) $entityType) ?>">
        <input type="hidden" name="entity_id" value="<?= e((string) ($entity['id'] ?? 0)) ?>">

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Meta Title</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="meta_title" value="<?= e((string) ($seo['meta_title'] ?? '')) ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Canonical URL</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="canonical_url" value="<?= e((string) ($seo['canonical_url'] ?? '')) ?>">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Meta Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="meta_description"><?= e((string) ($seo['meta_description'] ?? '')) ?></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Meta Keywords</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="meta_keywords" value="<?= e((string) ($seo['meta_keywords'] ?? '')) ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Robots</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="robots" value="<?= e((string) ($seo['robots'] ?? 'index,follow')) ?>">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">OG Title</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="og_title" value="<?= e((string) ($seo['og_title'] ?? '')) ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">OG Image</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="og_image_id">
                    <option value="">Select media</option>
                    <?php foreach ($media as $item): ?>
                        <option value="<?= e((string) ($item['id'] ?? '')) ?>" <?= ((string) ($seo['og_image_id'] ?? '') === (string) ($item['id'] ?? '')) ? 'selected' : '' ?>>
                            <?= e((string) ($item['original_name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">OG Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="og_description"><?= e((string) ($seo['og_description'] ?? '')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Schema JSON-LD</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="6" name="schema_json"><?= e((string) ($seo['schema_json'] ?? '')) ?></textarea>
        </div>

        <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Save SEO</button>
    </form>
</section>

