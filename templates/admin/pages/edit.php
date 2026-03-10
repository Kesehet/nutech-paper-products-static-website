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
                <?php
                $sectionJsonRaw = (string) ($section['content_json'] ?? '{}');
                $decoded = json_decode($sectionJsonRaw, true);
                $simplePairs = [];
                $supportsKeyValue = is_array($decoded);
                if ($supportsKeyValue) {
                    foreach ($decoded as $k => $v) {
                        if (!is_scalar($v) && $v !== null) {
                            $supportsKeyValue = false;
                            break;
                        }
                    }
                }
                if ($supportsKeyValue && is_array($decoded)) {
                    foreach ($decoded as $k => $v) {
                        $simplePairs[] = ['key' => (string) $k, 'value' => $v === null ? '' : (string) $v];
                    }
                }
                ?>
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

                    <div data-section-editor>
                        <label class="block text-xs font-semibold text-slate-500 mb-2">Content Fields</label>
                        <?php if ($supportsKeyValue): ?>
                            <div class="space-y-2" data-kv-list>
                                <?php if ($simplePairs === []): ?>
                                    <div class="grid md:grid-cols-2 gap-2" data-kv-row>
                                        <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="key" data-kv-key>
                                        <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="value" data-kv-value>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($simplePairs as $pair): ?>
                                        <div class="grid md:grid-cols-2 gap-2" data-kv-row>
                                            <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" value="<?= e((string) $pair['key']) ?>" data-kv-key>
                                            <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" value="<?= e((string) $pair['value']) ?>" data-kv-value>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button class="mt-2 text-xs font-semibold text-primary hover:underline" type="button" data-add-kv>Add Field</button>
                        <?php else: ?>
                            <p class="text-xs text-amber-600 mb-2">This section contains nested JSON. Use Advanced JSON editor for this block.</p>
                        <?php endif; ?>

                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-slate-600">Advanced JSON</summary>
                            <textarea class="mt-2 w-full rounded-lg border-slate-200 bg-slate-50 text-sm font-mono" rows="7" <?= $supportsKeyValue ? '' : 'name="section_content[]"' ?> data-json-target><?= e($sectionJsonRaw) ?></textarea>
                        </details>
                        <?php if ($supportsKeyValue): ?>
                            <textarea class="hidden" name="section_content[]" data-json-target><?= e($sectionJsonRaw) ?></textarea>
                        <?php endif; ?>
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
<script>
(() => {
    function parseMaybeJson(value) {
        try {
            const parsed = JSON.parse(value);
            return typeof parsed === "object" && parsed !== null ? parsed : {};
        } catch (_) {
            return {};
        }
    }

    document.querySelectorAll("[data-section-editor]").forEach((editor) => {
        const list = editor.querySelector("[data-kv-list]");
        const addBtn = editor.querySelector("[data-add-kv]");
        const jsonTargets = editor.querySelectorAll("[data-json-target]");
        if (!list || jsonTargets.length === 0) {
            return;
        }

        const sync = () => {
            const payload = {};
            list.querySelectorAll("[data-kv-row]").forEach((row) => {
                const keyEl = row.querySelector("[data-kv-key]");
                const valueEl = row.querySelector("[data-kv-value]");
                if (!keyEl || !valueEl) {
                    return;
                }
                const key = (keyEl.value || "").trim();
                if (key === "") {
                    return;
                }
                payload[key] = valueEl.value || "";
            });
            const json = JSON.stringify(payload, null, 2);
            jsonTargets.forEach((target) => {
                target.value = json;
            });
        };

        jsonTargets.forEach((target) => {
            target.addEventListener("input", () => {
                const current = target.value || "{}";
                jsonTargets.forEach((other) => {
                    if (other !== target) {
                        other.value = current;
                    }
                });
            });
        });

        const bindRow = (row) => {
            row.querySelectorAll("input").forEach((input) => {
                input.addEventListener("input", sync);
            });
        };

        list.querySelectorAll("[data-kv-row]").forEach(bindRow);

        if (addBtn) {
            addBtn.addEventListener("click", () => {
                const row = document.createElement("div");
                row.className = "grid md:grid-cols-2 gap-2";
                row.setAttribute("data-kv-row", "");
                row.innerHTML = `
                    <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="key" data-kv-key>
                    <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" placeholder="value" data-kv-value>
                `;
                list.appendChild(row);
                bindRow(row);
            });
        }

        const firstTarget = jsonTargets[0];
        if (firstTarget && list.querySelectorAll("[data-kv-row]").length === 0) {
            const parsed = parseMaybeJson(firstTarget.value || "{}");
            Object.keys(parsed).forEach((key) => {
                const row = document.createElement("div");
                row.className = "grid md:grid-cols-2 gap-2";
                row.setAttribute("data-kv-row", "");
                row.innerHTML = `
                    <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" data-kv-key>
                    <input class="rounded-lg border-slate-200 bg-slate-50 text-sm" type="text" data-kv-value>
                `;
                row.querySelector("[data-kv-key]").value = key;
                row.querySelector("[data-kv-value]").value = String(parsed[key] ?? "");
                list.appendChild(row);
                bindRow(row);
            });
        }

        sync();
    });
})();
</script>
