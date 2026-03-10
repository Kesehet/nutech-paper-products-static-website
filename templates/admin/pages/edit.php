<?php
declare(strict_types=1);
?>
<section class="space-y-6">
    <form action="<?= e(path_url('/admin/pages/' . (string) ($page['id'] ?? 0) . '/update')) ?>" method="post" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 space-y-6">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-dark-navy">Edit Page</h2>
                <p class="text-sm text-slate-600 mt-1">Update page title and structured section content.</p>
            </div>
            <a href="<?= e(path_url('/admin/pages')) ?>" class="text-sm font-semibold text-primary hover:underline">Back to Pages</a>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Page Title</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="title" value="<?= e((string) ($page['title'] ?? '')) ?>" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Publish Status</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="is_published">
                    <option value="1" <?= ((int) ($page['is_published'] ?? 0) === 1) ? 'selected' : '' ?>>Published</option>
                    <option value="0" <?= ((int) ($page['is_published'] ?? 0) === 0) ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
        </div>

        <div class="space-y-5">
            <?php foreach ($sections as $index => $section): ?>
                <div class="rounded-xl border border-slate-200 p-4 space-y-3">
                    <input type="hidden" name="section_id[]" value="<?= e((string) ($section['id'] ?? '')) ?>">
                    <div class="grid md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Section Key</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-100 text-sm" value="<?= e((string) ($section['section_key'] ?? '')) ?>" disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Label</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="section_label[]" value="<?= e((string) ($section['section_label'] ?? '')) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort Order</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="number" name="section_sort_order[]" value="<?= e((string) ($section['sort_order'] ?? 0)) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Visible</label>
                            <select class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="section_visible[]">
                                <option value="1" <?= ((int) ($section['is_visible'] ?? 0) === 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ((int) ($section['is_visible'] ?? 0) === 0) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Content JSON</label>
                        <textarea class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm font-mono" rows="6" name="section_content[]"><?= e((string) ($section['content_json'] ?? '{}')) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <details class="rounded-xl border border-dashed border-slate-300 p-4">
            <summary class="cursor-pointer font-semibold text-slate-700">Add New Section (Optional)</summary>
            <div class="mt-4 space-y-3">
                <div class="grid md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Section Key</label>
                        <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="new_section_key" placeholder="home.new-block">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Section Label</label>
                        <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="new_section_label" placeholder="New Block">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort</label>
                            <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="number" name="new_section_sort_order" value="0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Visible</label>
                            <select class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="new_section_visible">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Content JSON</label>
                    <textarea class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm font-mono" rows="5" name="new_section_content" placeholder='{"heading":"...","description":"..."}'></textarea>
                </div>
            </div>
        </details>

        <div>
            <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Save Changes</button>
        </div>
    </form>
</section>

