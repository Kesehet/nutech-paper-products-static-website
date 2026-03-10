<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy">Pages / Content</h2>
            <p class="text-sm text-slate-600 mt-1">Edit page-level content blocks and visibility.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="py-3 pr-4">Page</th>
                    <th class="py-3 pr-4">Slug</th>
                    <th class="py-3 pr-4">Template</th>
                    <th class="py-3 pr-4">Sections</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pages as $page): ?>
                <tr class="border-b border-slate-100">
                    <td class="py-3 pr-4 font-semibold text-slate-800"><?= e((string) ($page['title'] ?? '')) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($page['slug'] ?? '')) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($page['template_key'] ?? '')) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= e((string) ($page['section_count'] ?? 0)) ?></td>
                    <td class="py-3 pr-4">
                        <?php if ((int) ($page['is_published'] ?? 0) === 1): ?>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Published</span>
                        <?php else: ?>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3">
                        <a class="text-primary font-semibold hover:underline" href="<?= e(path_url('/admin/pages/' . (string) ($page['id'] ?? 0) . '/edit')) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

