<?php
declare(strict_types=1);
?>
<section class="space-y-6">
    <form action="<?= e(path_url('/admin/media/upload')) ?>" method="post" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <h2 class="text-2xl font-black text-dark-navy mb-2">Media Library</h2>
        <p class="text-sm text-slate-600 mb-5">Upload images/documents and reuse them across pages/products.</p>
        <div class="grid md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Select File</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-primary file:text-dark-navy file:font-semibold" type="file" name="media_file" required>
            </div>
            <div>
                <button class="w-full px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Upload</button>
            </div>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <h3 class="text-xl font-black text-dark-navy mb-4">Uploaded Files</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-200">
                        <th class="py-3 pr-4">Preview</th>
                        <th class="py-3 pr-4">File</th>
                        <th class="py-3 pr-4">Type</th>
                        <th class="py-3 pr-4">Size</th>
                        <th class="py-3 pr-4">Dimensions</th>
                        <th class="py-3 pr-4">Uploaded</th>
                        <th class="py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($media as $item): ?>
                    <tr class="border-b border-slate-100">
                        <td class="py-3 pr-4">
                            <?php if (str_starts_with((string) ($item['mime_type'] ?? ''), 'image/')): ?>
                                <img src="<?= e(path_url((string) ($item['storage_path'] ?? ''))) ?>" alt="" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                            <?php else: ?>
                                <span class="inline-flex px-2 py-1 text-xs rounded bg-slate-100 text-slate-600">File</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 pr-4">
                            <p class="font-semibold text-slate-800"><?= e((string) ($item['original_name'] ?? '')) ?></p>
                            <p class="text-xs text-slate-500"><?= e((string) ($item['storage_path'] ?? '')) ?></p>
                        </td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($item['mime_type'] ?? '')) ?></td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) round(((int) ($item['size_bytes'] ?? 0)) / 1024, 1)) ?> KB</td>
                        <td class="py-3 pr-4 text-slate-600">
                            <?php if (!empty($item['width']) && !empty($item['height'])): ?>
                                <?= e((string) $item['width']) ?> x <?= e((string) $item['height']) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($item['created_at'] ?? '')) ?></td>
                        <td class="py-3">
                            <form method="post" action="<?= e(path_url('/admin/media/' . (string) ($item['id'] ?? 0) . '/delete')) ?>" onsubmit="return confirm('Delete this media file?');">
                                <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                                <button class="text-red-600 font-semibold hover:underline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

