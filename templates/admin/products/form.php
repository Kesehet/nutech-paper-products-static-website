<?php
declare(strict_types=1);

$specJson = (string) ($product['specifications_json'] ?? '{}');
$featureJson = (string) ($product['features_json'] ?? '[]');
$applicationJson = (string) ($product['applications_json'] ?? '[]');

$specDecoded = json_decode($specJson, true);
if (!is_array($specDecoded)) {
    $specDecoded = [];
}
if (array_key_exists('raw_text', $specDecoded)) {
    $specDecoded = [];
}

$featureDecoded = json_decode($featureJson, true);
if (!is_array($featureDecoded)) {
    $featureDecoded = [];
}
if (array_key_exists('raw_text', $featureDecoded)) {
    $featureDecoded = [];
}

$applicationDecoded = json_decode($applicationJson, true);
if (!is_array($applicationDecoded)) {
    $applicationDecoded = [];
}
if (array_key_exists('raw_text', $applicationDecoded)) {
    $applicationDecoded = [];
}

$featuredImageId = (int) ($product['featured_image_id'] ?? 0);
$mediaLookup = [];
foreach ($mediaLibrary as $mediaRow) {
    $id = (int) ($mediaRow['id'] ?? 0);
    if ($id > 0) {
        $mediaLookup[$id] = $mediaRow;
    }
}
$featuredMedia = $mediaLookup[$featuredImageId] ?? null;
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy"><?= e((string) ($submitLabel ?? 'Save Product')) ?></h2>
            <p class="text-sm text-slate-600 mt-1">Maintain product details used on catalog and product detail pages.</p>
        </div>
        <a href="<?= e(path_url('/admin/products')) ?>" class="text-sm font-semibold text-primary hover:underline">Back to Products</a>
    </div>

    <form id="product-form" action="<?= e(path_url((string) $formAction)) ?>" method="post" class="space-y-5">
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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Featured Image</h3>
                    <p class="text-xs text-slate-500">Pick one image from media library.</p>
                </div>
                <button type="button" id="open-featured-modal" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Choose Image</button>
            </div>
            <input type="hidden" id="featured_image_id" name="featured_image_id" value="<?= e((string) $featuredImageId) ?>">
            <div id="featured-preview" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 flex items-center gap-3">
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
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Gallery Images</h3>
                    <p class="text-xs text-slate-500">Select multiple media items, then set alt text and order.</p>
                </div>
                <button type="button" id="open-gallery-modal" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Add / Remove Images</button>
            </div>
            <div id="gallery-fields" class="mt-3 space-y-3"></div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Short Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="short_description"><?= e((string) ($product['short_description'] ?? '')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Long Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="6" name="long_description"><?= e((string) ($product['long_description'] ?? '')) ?></textarea>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 space-y-5">
            <h3 class="text-sm font-bold text-slate-800">Structured Content Editor</h3>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold">Specifications</label>
                    <button type="button" id="add-spec-row" class="text-xs font-semibold text-primary hover:underline">Add Row</button>
                </div>
                <div id="spec-rows" class="space-y-2"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold">Features</label>
                    <button type="button" id="add-feature-row" class="text-xs font-semibold text-primary hover:underline">Add Item</button>
                </div>
                <div id="feature-rows" class="space-y-2"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold">Applications</label>
                    <button type="button" id="add-application-row" class="text-xs font-semibold text-primary hover:underline">Add Item</button>
                </div>
                <div id="application-rows" class="space-y-2"></div>
            </div>

            <details>
                <summary class="cursor-pointer text-xs font-semibold text-slate-600">Advanced JSON (manual override)</summary>
                <div class="grid md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Specifications JSON</label>
                        <textarea id="advanced-spec-json" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7"><?= e($specJson) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Features JSON</label>
                        <textarea id="advanced-feature-json" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7"><?= e($featureJson) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Applications JSON</label>
                        <textarea id="advanced-application-json" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="7"><?= e($applicationJson) ?></textarea>
                    </div>
                </div>
                <button type="button" id="apply-advanced-json" class="mt-3 px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold hover:bg-slate-50">Apply Advanced JSON</button>
            </details>

            <textarea name="specifications_json" id="specifications_json" class="hidden"><?= e($specJson) ?></textarea>
            <textarea name="features_json" id="features_json" class="hidden"><?= e($featureJson) ?></textarea>
            <textarea name="applications_json" id="applications_json" class="hidden"><?= e($applicationJson) ?></textarea>
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

<script>
(() => {
    const mediaLibrary = <?= json_encode(array_values($mediaLibrary), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const gallerySelections = <?= json_encode($gallerySelections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    let featuredImageId = Number(document.getElementById("featured_image_id").value || 0);
    let gallerySelectedIds = Object.keys(gallerySelections).map((id) => Number(id));
    let galleryMeta = {};
    let galleryPrimaryId = 0;

    Object.keys(gallerySelections).forEach((id) => {
        const numId = Number(id);
        const row = gallerySelections[id] || {};
        galleryMeta[numId] = {
            alt_text: String(row.alt_text || ""),
            sort_order: Number(row.sort_order || 0)
        };
        if (row.is_primary) {
            galleryPrimaryId = numId;
        }
    });

    const mediaById = {};
    mediaLibrary.forEach((m) => {
        mediaById[Number(m.id)] = m;
    });

    const modal = document.getElementById("media-picker-modal");
    const modalTitle = document.getElementById("media-modal-title");
    const modalGrid = document.getElementById("media-modal-grid");
    const modalSearch = document.getElementById("media-modal-search");

    let modalMode = "featured";
    let tempFeatured = featuredImageId;
    let tempGallery = new Set(gallerySelectedIds);

    const featuredPreview = document.getElementById("featured-preview");
    const featuredInput = document.getElementById("featured_image_id");
    const galleryFields = document.getElementById("gallery-fields");

    function openModal(mode) {
        modalMode = mode;
        tempFeatured = featuredImageId;
        tempGallery = new Set(gallerySelectedIds);
        modalTitle.textContent = mode === "featured" ? "Select Featured Image" : "Select Gallery Images";
        modal.classList.remove("hidden");
        modalSearch.value = "";
        renderMediaCards();
    }

    function closeModal() {
        modal.classList.add("hidden");
    }

    function renderMediaCards() {
        const q = (modalSearch.value || "").trim().toLowerCase();
        modalGrid.innerHTML = "";
        mediaLibrary.forEach((item) => {
            const name = String(item.original_name || "");
            const path = String(item.storage_path || "");
            const matches = q === "" || name.toLowerCase().includes(q) || path.toLowerCase().includes(q);
            if (!matches) {
                return;
            }

            const id = Number(item.id);
            const selected = modalMode === "featured" ? tempFeatured === id : tempGallery.has(id);

            const card = document.createElement("button");
            card.type = "button";
            card.className = "text-left rounded-xl border p-2 transition-colors " + (selected ? "border-primary bg-primary/5" : "border-slate-200 hover:border-slate-300");
            card.dataset.id = String(id);

            const isImage = String(item.mime_type || "").startsWith("image/");
            card.innerHTML = `
                <div class="h-24 rounded-lg overflow-hidden border border-slate-200 bg-slate-50 mb-2 flex items-center justify-center">
                    ${isImage ? `<img src="${encodeURI(path_url_js(path))}" alt="" class="w-full h-full object-cover">` : `<span class="text-xs text-slate-500">FILE</span>`}
                </div>
                <p class="text-xs text-slate-500">#${id}</p>
                <p class="text-sm font-semibold text-slate-800 truncate">${escapeHtml(name)}</p>
            `;

            card.addEventListener("click", () => {
                if (modalMode === "featured") {
                    tempFeatured = id;
                } else {
                    if (tempGallery.has(id)) {
                        tempGallery.delete(id);
                    } else {
                        tempGallery.add(id);
                    }
                }
                renderMediaCards();
            });
            modalGrid.appendChild(card);
        });
    }

    function renderFeaturedPreview() {
        const row = mediaById[featuredImageId];
        featuredInput.value = featuredImageId > 0 ? String(featuredImageId) : "";
        if (!row) {
            featuredPreview.innerHTML = `<p class="text-sm text-slate-500">No featured image selected.</p>`;
            return;
        }
        const isImage = String(row.mime_type || "").startsWith("image/");
        featuredPreview.innerHTML = `
            ${isImage ? `<img src="${encodeURI(path_url_js(String(row.storage_path || "")))}" alt="" class="w-16 h-16 rounded object-cover border border-slate-200">` : `<span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs">File</span>`}
            <div>
                <p class="text-xs text-slate-500">#${Number(row.id || 0)}</p>
                <p class="text-sm font-semibold text-slate-800">${escapeHtml(String(row.original_name || ""))}</p>
            </div>
        `;
    }

    function ensurePrimarySelection() {
        if (gallerySelectedIds.length === 0) {
            galleryPrimaryId = 0;
            return;
        }
        if (!gallerySelectedIds.includes(galleryPrimaryId)) {
            galleryPrimaryId = gallerySelectedIds[0];
        }
    }

    function renderGalleryFields() {
        ensurePrimarySelection();
        galleryFields.innerHTML = "";
        if (gallerySelectedIds.length === 0) {
            galleryFields.innerHTML = `<p class="text-sm text-slate-500">No gallery images selected.</p>`;
            return;
        }

        const sorted = [...gallerySelectedIds].sort((a, b) => {
            const aSort = Number((galleryMeta[a] || {}).sort_order || 0);
            const bSort = Number((galleryMeta[b] || {}).sort_order || 0);
            if (aSort === bSort) {
                return a - b;
            }
            return aSort - bSort;
        });

        sorted.forEach((id, index) => {
            const media = mediaById[id];
            if (!media) {
                return;
            }
            if (!galleryMeta[id]) {
                galleryMeta[id] = { alt_text: "", sort_order: index + 1 };
            }
            if (!galleryMeta[id].sort_order || Number(galleryMeta[id].sort_order) <= 0) {
                galleryMeta[id].sort_order = index + 1;
            }
            const meta = galleryMeta[id];
            const isImage = String(media.mime_type || "").startsWith("image/");
            const row = document.createElement("div");
            row.className = "grid md:grid-cols-12 gap-3 border border-slate-100 rounded-lg p-3 items-center";
            row.innerHTML = `
                <div class="md:col-span-4 flex items-center gap-3">
                    <input type="hidden" name="gallery_media_id[]" value="${id}">
                    ${isImage ? `<img src="${encodeURI(path_url_js(String(media.storage_path || "")))}" alt="" class="w-12 h-12 object-cover rounded border border-slate-200">` : `<span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs">File</span>`}
                    <div>
                        <p class="text-xs text-slate-500">#${id}</p>
                        <p class="text-sm font-semibold text-slate-800">${escapeHtml(String(media.original_name || ""))}</p>
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Alt Text</label>
                    <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="gallery_alt_text[${id}]" value="${escapeHtml(meta.alt_text || "")}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Sort</label>
                    <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="number" name="gallery_sort_order[${id}]" value="${Number(meta.sort_order || 0)}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Primary</label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="gallery_primary_media_id" value="${id}" ${galleryPrimaryId === id ? "checked" : ""}>
                        Primary
                    </label>
                </div>
                <div class="md:col-span-1">
                    <button type="button" class="text-red-600 text-xs font-semibold hover:underline" data-remove-id="${id}">Remove</button>
                </div>
            `;

            row.querySelector(`input[name="gallery_alt_text[${id}]"]`).addEventListener("input", (e) => {
                galleryMeta[id].alt_text = e.target.value || "";
            });
            row.querySelector(`input[name="gallery_sort_order[${id}]"]`).addEventListener("input", (e) => {
                galleryMeta[id].sort_order = Number(e.target.value || 0);
            });
            row.querySelector(`input[name="gallery_primary_media_id"]`).addEventListener("change", () => {
                galleryPrimaryId = id;
            });
            row.querySelector(`[data-remove-id="${id}"]`).addEventListener("click", () => {
                gallerySelectedIds = gallerySelectedIds.filter((x) => x !== id);
                delete galleryMeta[id];
                if (galleryPrimaryId === id) {
                    galleryPrimaryId = 0;
                }
                renderGalleryFields();
            });

            galleryFields.appendChild(row);
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function path_url_js(path) {
        if (!path) return "";
        if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("//")) {
            return path;
        }
        const base = <?= json_encode(app_base_path(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const normalized = "/" + String(path).replace(/^\/+/, "");
        return (base || "") + normalized;
    }

    document.getElementById("open-featured-modal").addEventListener("click", () => openModal("featured"));
    document.getElementById("open-gallery-modal").addEventListener("click", () => openModal("gallery"));
    document.getElementById("close-media-modal").addEventListener("click", closeModal);
    document.getElementById("cancel-media-modal").addEventListener("click", closeModal);

    document.getElementById("apply-media-modal").addEventListener("click", () => {
        if (modalMode === "featured") {
            featuredImageId = tempFeatured;
            renderFeaturedPreview();
        } else {
            gallerySelectedIds = Array.from(tempGallery);
            gallerySelectedIds.forEach((id, idx) => {
                if (!galleryMeta[id]) {
                    galleryMeta[id] = { alt_text: "", sort_order: idx + 1 };
                }
            });
            Object.keys(galleryMeta).forEach((id) => {
                if (!gallerySelectedIds.includes(Number(id))) {
                    delete galleryMeta[id];
                }
            });
            renderGalleryFields();
        }
        closeModal();
    });

    modalSearch.addEventListener("input", renderMediaCards);

    // JSON editors
    const specRows = document.getElementById("spec-rows");
    const featureRows = document.getElementById("feature-rows");
    const appRows = document.getElementById("application-rows");
    const specsHidden = document.getElementById("specifications_json");
    const featuresHidden = document.getElementById("features_json");
    const applicationsHidden = document.getElementById("applications_json");

    function addSpecRow(key = "", value = "") {
        const row = document.createElement("div");
        row.className = "grid md:grid-cols-2 gap-2";
        row.innerHTML = `
            <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="Label" data-spec-key value="${escapeHtml(key)}">
            <div class="flex gap-2">
                <input class="flex-1 rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="Value" data-spec-value value="${escapeHtml(value)}">
                <button type="button" class="px-3 rounded border border-slate-300 text-xs" data-remove>Remove</button>
            </div>
        `;
        row.querySelectorAll("input").forEach((el) => el.addEventListener("input", syncSpecJson));
        row.querySelector("[data-remove]").addEventListener("click", () => {
            row.remove();
            syncSpecJson();
        });
        specRows.appendChild(row);
    }

    function addListRow(container, onSync, value = "") {
        const row = document.createElement("div");
        row.className = "flex gap-2";
        row.innerHTML = `
            <input class="flex-1 rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" value="${escapeHtml(value)}">
            <button type="button" class="px-3 rounded border border-slate-300 text-xs" data-remove>Remove</button>
        `;
        row.querySelector("input").addEventListener("input", onSync);
        row.querySelector("[data-remove]").addEventListener("click", () => {
            row.remove();
            onSync();
        });
        container.appendChild(row);
    }

    function syncSpecJson() {
        const payload = {};
        specRows.querySelectorAll(".grid").forEach((row) => {
            const key = (row.querySelector("[data-spec-key]").value || "").trim();
            const value = row.querySelector("[data-spec-value]").value || "";
            if (key !== "") {
                payload[key] = value;
            }
        });
        specsHidden.value = JSON.stringify(payload, null, 2);
        document.getElementById("advanced-spec-json").value = specsHidden.value;
    }

    function syncListJson(container, hiddenTarget, advancedId) {
        const payload = [];
        container.querySelectorAll("input").forEach((input) => {
            const value = (input.value || "").trim();
            if (value !== "") {
                payload.push(value);
            }
        });
        hiddenTarget.value = JSON.stringify(payload, null, 2);
        document.getElementById(advancedId).value = hiddenTarget.value;
    }

    function safeParse(json, fallback) {
        try {
            const parsed = JSON.parse(json);
            return parsed;
        } catch (_) {
            return fallback;
        }
    }

    function initJsonEditors() {
        const specs = safeParse(specsHidden.value || "{}", {});
        if (specs && typeof specs === "object" && !Array.isArray(specs)) {
            const keys = Object.keys(specs);
            if (keys.length === 0) {
                addSpecRow();
            } else {
                keys.forEach((k) => addSpecRow(k, String(specs[k] ?? "")));
            }
        } else {
            addSpecRow();
        }

        const features = safeParse(featuresHidden.value || "[]", []);
        if (Array.isArray(features) && features.length > 0) {
            features.forEach((v) => addListRow(featureRows, () => syncListJson(featureRows, featuresHidden, "advanced-feature-json"), String(v ?? "")));
        } else {
            addListRow(featureRows, () => syncListJson(featureRows, featuresHidden, "advanced-feature-json"));
        }

        const applications = safeParse(applicationsHidden.value || "[]", []);
        if (Array.isArray(applications) && applications.length > 0) {
            applications.forEach((v) => addListRow(appRows, () => syncListJson(appRows, applicationsHidden, "advanced-application-json"), String(v ?? "")));
        } else {
            addListRow(appRows, () => syncListJson(appRows, applicationsHidden, "advanced-application-json"));
        }

        syncSpecJson();
        syncListJson(featureRows, featuresHidden, "advanced-feature-json");
        syncListJson(appRows, applicationsHidden, "advanced-application-json");
    }

    document.getElementById("add-spec-row").addEventListener("click", () => addSpecRow());
    document.getElementById("add-feature-row").addEventListener("click", () => addListRow(featureRows, () => syncListJson(featureRows, featuresHidden, "advanced-feature-json")));
    document.getElementById("add-application-row").addEventListener("click", () => addListRow(appRows, () => syncListJson(appRows, applicationsHidden, "advanced-application-json")));

    document.getElementById("apply-advanced-json").addEventListener("click", () => {
        const specAdvanced = document.getElementById("advanced-spec-json").value || "{}";
        const featureAdvanced = document.getElementById("advanced-feature-json").value || "[]";
        const appAdvanced = document.getElementById("advanced-application-json").value || "[]";

        specsHidden.value = specAdvanced;
        featuresHidden.value = featureAdvanced;
        applicationsHidden.value = appAdvanced;

        specRows.innerHTML = "";
        featureRows.innerHTML = "";
        appRows.innerHTML = "";
        initJsonEditors();
    });

    document.getElementById("product-form").addEventListener("submit", () => {
        if (gallerySelectedIds.length > 0 && !galleryPrimaryId) {
            galleryPrimaryId = gallerySelectedIds[0];
        }
        syncSpecJson();
        syncListJson(featureRows, featuresHidden, "advanced-feature-json");
        syncListJson(appRows, applicationsHidden, "advanced-application-json");
    });

    initJsonEditors();
    renderFeaturedPreview();
    renderGalleryFields();
})();
</script>

