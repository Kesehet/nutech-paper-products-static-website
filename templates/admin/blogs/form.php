<?php
declare(strict_types=1);

$featuredImageId = (int) ($blog['featured_image_id'] ?? 0);
$ogImageId = (int) ($blog['og_image_id'] ?? 0);
$mediaLookup = [];
foreach ($mediaLibrary as $mediaRow) {
    $id = (int) ($mediaRow['id'] ?? 0);
    if ($id > 0) {
        $mediaLookup[$id] = $mediaRow;
    }
}
$featuredMedia = $mediaLookup[$featuredImageId] ?? null;
$ogMedia = $mediaLookup[$ogImageId] ?? null;
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy"><?= e((string) ($submitLabel ?? 'Save Blog')) ?></h2>
            <p class="text-sm text-slate-600 mt-1">Use CKEditor for rich content, upload images inline, and manage per-post SEO in one place.</p>
        </div>
        <a href="<?= e(path_url('/admin/blogs')) ?>" class="text-sm font-semibold text-primary hover:underline">Back to Blogs</a>
    </div>

    <form id="blog-form" action="<?= e(path_url((string) $formAction)) ?>" method="post" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Title *</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="title" value="<?= e((string) ($blog['title'] ?? '')) ?>" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Slug</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="slug" value="<?= e((string) ($blog['slug'] ?? '')) ?>" placeholder="auto-generated-if-empty">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Status</label>
                <?php $status = (string) ($blog['status'] ?? 'draft'); ?>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Publish Date</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="datetime-local" name="published_at" value="<?= e(!empty($blog['published_at']) ? date('Y-m-d\TH:i', strtotime((string) $blog['published_at'])) : '') ?>">
                <p class="text-xs text-slate-500 mt-1">Leave blank to publish immediately when status is set to published.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Excerpt</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="excerpt"><?= e((string) ($blog['excerpt'] ?? '')) ?></textarea>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-slate-200 p-4 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Featured Image</h3>
                        <p class="text-xs text-slate-500">Choose from media library or upload instantly here.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-open-picker="featured" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Choose Image</button>
                        <button type="button" data-trigger-upload="featured" class="px-4 py-2 rounded-lg bg-primary text-dark-navy text-sm font-bold hover:bg-primary-hover">Upload / Paste</button>
                    </div>
                </div>
                <input type="hidden" id="featured_image_id" name="featured_image_id" value="<?= e((string) $featuredImageId) ?>">
                <input type="file" data-upload-input="featured" accept="image/*" class="hidden">
                <div id="featured-preview" class="rounded-lg border border-slate-200 bg-slate-50 p-3 min-h-24 flex items-center gap-3">
                    <?php if (is_array($featuredMedia)): ?>
                        <img src="<?= e(path_url((string) ($featuredMedia['storage_path'] ?? ''))) ?>" alt="" class="w-16 h-16 rounded object-cover border border-slate-200">
                        <div>
                            <p class="text-xs text-slate-500">#<?= e((string) ($featuredMedia['id'] ?? '')) ?></p>
                            <p class="text-sm font-semibold text-slate-800"><?= e((string) ($featuredMedia['original_name'] ?? '')) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-slate-500">No featured image selected.</p>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-500">Tip: click inside this page and paste an image from your clipboard to upload it directly.</p>
            </div>

            <div class="rounded-xl border border-slate-200 p-4 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Open Graph Image</h3>
                        <p class="text-xs text-slate-500">Optional social sharing image for this blog post.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-open-picker="og" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Choose Image</button>
                        <button type="button" data-trigger-upload="og" class="px-4 py-2 rounded-lg bg-primary text-dark-navy text-sm font-bold hover:bg-primary-hover">Upload / Paste</button>
                    </div>
                </div>
                <input type="hidden" id="og_image_id" name="og_image_id" value="<?= e((string) $ogImageId) ?>">
                <input type="file" data-upload-input="og" accept="image/*" class="hidden">
                <div id="og-preview" class="rounded-lg border border-slate-200 bg-slate-50 p-3 min-h-24 flex items-center gap-3">
                    <?php if (is_array($ogMedia)): ?>
                        <img src="<?= e(path_url((string) ($ogMedia['storage_path'] ?? ''))) ?>" alt="" class="w-16 h-16 rounded object-cover border border-slate-200">
                        <div>
                            <p class="text-xs text-slate-500">#<?= e((string) ($ogMedia['id'] ?? '')) ?></p>
                            <p class="text-sm font-semibold text-slate-800"><?= e((string) ($ogMedia['original_name'] ?? '')) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-slate-500">No Open Graph image selected.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Body Content</h3>
                    <p class="text-xs text-slate-500">Rich editor with inline image uploads via CKEditor 4.</p>
                </div>
                <span id="upload-feedback" class="text-xs text-slate-500"></span>
            </div>
            <textarea id="content_html" name="content_html" rows="16" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary"><?= e((string) ($blog['content_html'] ?? '')) ?></textarea>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">SEO</h3>
                <p class="text-xs text-slate-500">Store blog-specific metadata directly on this post.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Meta Title</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="seo_title" value="<?= e((string) ($blog['seo_title'] ?? '')) ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Canonical URL</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="canonical_url" value="<?= e((string) ($blog['canonical_url'] ?? '')) ?>" placeholder="https://example.com/blog/your-post">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Meta Description</label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="seo_description"><?= e((string) ($blog['seo_description'] ?? '')) ?></textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Meta Keywords</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="seo_keywords" value="<?= e((string) ($blog['seo_keywords'] ?? '')) ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Robots</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="robots" value="<?= e((string) ($blog['robots'] ?? 'index,follow')) ?>">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">OG Title</label>
                    <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="og_title" value="<?= e((string) ($blog['og_title'] ?? '')) ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">OG Description</label>
                    <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="og_description"><?= e((string) ($blog['og_description'] ?? '')) ?></textarea>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Schema JSON-LD</label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="8" name="schema_json"><?= e((string) ($blog['schema_json'] ?? '')) ?></textarea>
            </div>
        </div>

        <div>
            <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit"><?= e((string) $submitLabel) ?></button>
        </div>
    </form>
</section>

<div id="media-picker-modal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-slate-900/50"></div>
    <div class="relative max-w-5xl mx-auto mt-10 mb-10 bg-white rounded-2xl border border-slate-200 shadow-2xl h-[80vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 id="media-modal-title" class="text-lg font-bold text-dark-navy">Select Media</h3>
                <p class="text-xs text-slate-500">Choose from uploaded media library.</p>
            </div>
            <button type="button" id="close-media-modal" class="px-3 py-1.5 rounded border border-slate-300 text-sm">Close</button>
        </div>
        <div class="p-4 border-b border-slate-100">
            <input type="search" id="media-modal-search" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" placeholder="Search media...">
        </div>
        <div id="media-modal-grid" class="flex-1 overflow-auto p-4 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"></div>
        <div class="p-4 border-t border-slate-200 flex justify-end gap-2">
            <button type="button" id="cancel-media-modal" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold">Cancel</button>
            <button type="button" id="apply-media-modal" class="px-4 py-2 rounded-lg bg-primary text-dark-navy text-sm font-bold">Apply</button>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.25.1-lts/full-all/ckeditor.js"></script>
<script>
(() => {
    const csrfToken = <?= json_encode((string) $csrfToken) ?>;
    const mediaLibrary = <?= json_encode(array_values($mediaLibrary), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const mediaById = {};
    mediaLibrary.forEach((item) => {
        mediaById[Number(item.id)] = item;
    });

    const modal = document.getElementById('media-picker-modal');
    const modalGrid = document.getElementById('media-modal-grid');
    const modalSearch = document.getElementById('media-modal-search');
    const modalTitle = document.getElementById('media-modal-title');
    const uploadFeedback = document.getElementById('upload-feedback');
    let pickerMode = 'featured';
    let tempSelection = 0;

    function renderPreview(target, mediaId) {
        const input = document.getElementById(target + '_image_id');
        const preview = document.getElementById(target + '-preview');
        const media = mediaById[Number(mediaId)] || null;
        input.value = media ? String(media.id) : '';
        if (!media) {
            preview.innerHTML = '<p class="text-sm text-slate-500">No ' + (target === 'featured' ? 'featured' : 'Open Graph') + ' image selected.</p>';
            return;
        }

        preview.innerHTML = `
            <img src="${<?= json_encode(path_url('/')) ?>.replace(/\/$/, '')}${media.storage_path}" alt="" class="w-16 h-16 rounded object-cover border border-slate-200">
            <div>
                <p class="text-xs text-slate-500">#${media.id}</p>
                <p class="text-sm font-semibold text-slate-800">${media.original_name || ''}</p>
            </div>
        `;
    }

    function filteredMedia(term) {
        const needle = term.trim().toLowerCase();
        if (!needle) {
            return mediaLibrary;
        }
        return mediaLibrary.filter((item) => [item.original_name, item.storage_path, item.mime_type].join(' ').toLowerCase().includes(needle));
    }

    function renderModal() {
        const items = filteredMedia(modalSearch.value || '');
        modalGrid.innerHTML = items.map((item) => {
            const id = Number(item.id);
            const active = tempSelection === id;
            const thumb = String(item.mime_type || '').startsWith('image/')
                ? `<img src="${<?= json_encode(path_url('/')) ?>.replace(/\/$/, '')}${item.storage_path}" alt="" class="w-full h-32 object-cover rounded-lg border border-slate-200">`
                : '<div class="w-full h-32 rounded-lg border border-slate-200 bg-slate-100 flex items-center justify-center text-sm text-slate-500">File</div>';
            return `
                <button type="button" class="text-left rounded-xl border p-3 transition ${active ? 'border-primary ring-2 ring-primary/30 bg-primary/5' : 'border-slate-200 hover:border-primary/50'}" data-media-id="${id}">
                    ${thumb}
                    <p class="mt-2 text-sm font-semibold text-slate-800 line-clamp-1">${item.original_name || ''}</p>
                    <p class="text-xs text-slate-500 line-clamp-1">${item.storage_path || ''}</p>
                </button>
            `;
        }).join('');

        modalGrid.querySelectorAll('[data-media-id]').forEach((button) => {
            button.addEventListener('click', () => {
                tempSelection = Number(button.getAttribute('data-media-id') || 0);
                renderModal();
            });
        });
    }

    function openPicker(mode) {
        pickerMode = mode;
        tempSelection = Number(document.getElementById(mode + '_image_id').value || 0);
        modalTitle.textContent = mode === 'featured' ? 'Select Featured Image' : 'Select Open Graph Image';
        modal.classList.remove('hidden');
        modalSearch.value = '';
        renderModal();
    }

    function closePicker() {
        modal.classList.add('hidden');
    }

    document.querySelectorAll('[data-open-picker]').forEach((button) => {
        button.addEventListener('click', () => openPicker(button.getAttribute('data-open-picker') || 'featured'));
    });
    document.getElementById('close-media-modal').addEventListener('click', closePicker);
    document.getElementById('cancel-media-modal').addEventListener('click', closePicker);
    document.getElementById('apply-media-modal').addEventListener('click', () => {
        renderPreview(pickerMode, tempSelection);
        closePicker();
    });
    modalSearch.addEventListener('input', renderModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closePicker();
        }
    });

    async function uploadFile(file, target, insertIntoEditor = false) {
        const formData = new FormData();
        formData.append('_csrf', csrfToken);
        formData.append('media_file', file);
        uploadFeedback.textContent = 'Uploading ' + file.name + '...';

        const response = await fetch(<?= json_encode(path_url('/admin/media/upload-async')) ?>, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const payload = await response.json();
        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'Upload failed.');
        }

        mediaById[Number(payload.media.id)] = payload.media;
        mediaLibrary.unshift(payload.media);
        renderPreview(target, payload.media.id);
        if (insertIntoEditor && window.CKEDITOR && CKEDITOR.instances.content_html) {
            CKEDITOR.instances.content_html.insertHtml(`<p><img src="${payload.media.url}" alt=""></p>`);
        }
        uploadFeedback.textContent = 'Uploaded ' + file.name + ' successfully.';
        setTimeout(() => {
            uploadFeedback.textContent = '';
        }, 3000);
    }

    document.querySelectorAll('[data-trigger-upload]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.getAttribute('data-trigger-upload') || 'featured';
            const input = document.querySelector('[data-upload-input="' + target + '"]');
            if (input) {
                input.click();
            }
        });
    });

    document.querySelectorAll('[data-upload-input]').forEach((input) => {
        input.addEventListener('change', async () => {
            const target = input.getAttribute('data-upload-input') || 'featured';
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) {
                return;
            }
            try {
                await uploadFile(file, target, false);
            } catch (error) {
                uploadFeedback.textContent = error.message || 'Upload failed.';
            }
            input.value = '';
        });
    });

    document.addEventListener('paste', async (event) => {
        const items = event.clipboardData ? Array.from(event.clipboardData.items || []) : [];
        const imageItem = items.find((item) => item.type && item.type.startsWith('image/'));
        if (!imageItem) {
            return;
        }
        const file = imageItem.getAsFile();
        if (!file) {
            return;
        }
        try {
            const target = document.activeElement && document.activeElement.closest('[id="og-preview"]') ? 'og' : 'featured';
            await uploadFile(file, target, true);
        } catch (error) {
            uploadFeedback.textContent = error.message || 'Clipboard upload failed.';
        }
    });

    if (window.CKEDITOR) {
        CKEDITOR.replace('content_html', {
            height: 420,
            allowedContent: true,
            extraPlugins: 'uploadimage,image2,colorbutton,justify',
            removePlugins: 'easyimage',
            filebrowserImageUploadUrl: <?= json_encode(query_url('/admin/media/ckeditor-upload', ['_csrf' => (string) $csrfToken])) ?>,
            filebrowserUploadMethod: 'form',
        });
    }
})();
</script>
