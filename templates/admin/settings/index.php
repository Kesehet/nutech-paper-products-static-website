<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <h2 class="text-2xl font-black text-dark-navy mb-2">Site Settings</h2>
    <p class="text-sm text-slate-600 mb-6">All editable settings are listed below with friendly labels and default values.</p>

    <form action="<?= e(path_url('/admin/settings/save')) ?>" method="post" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">

        <div class="text-xs text-slate-500 mb-2">Total fields: <?= e((string) ($fieldCount ?? 0)) ?></div>

        <?php foreach (($sections ?? []) as $sectionName => $sectionFields): ?>
            <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 md:p-5">
                <h3 class="text-lg font-extrabold text-dark-navy mb-4"><?= e((string) $sectionName) ?></h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($sectionFields as $field): ?>
                        <?php
                        $inputType = (string) ($field['input'] ?? 'text');
                        $defaultValue = (string) ($field['default'] ?? '');
                        $defaultLabel = $defaultValue === '' ? '(empty)' : $defaultValue;
                        ?>
                        <div class="<?= $inputType === 'textarea' ? 'md:col-span-2' : '' ?>">
                            <label class="block text-sm font-semibold mb-1"><?= e((string) ($field['label'] ?? '')) ?></label>
                            <?php if ($inputType === 'textarea'): ?>
                                <textarea
                                    class="w-full rounded-xl border-slate-200 bg-white focus:ring-primary focus:border-primary"
                                    name="<?= e((string) ($field['input_name'] ?? '')) ?>"
                                    rows="3"
                                ><?= e((string) ($field['value'] ?? '')) ?></textarea>
                            <?php else: ?>
                                <input
                                    class="w-full rounded-xl border-slate-200 bg-white focus:ring-primary focus:border-primary <?= $inputType === 'color' ? 'h-12 p-1' : '' ?>"
                                    name="<?= e((string) ($field['input_name'] ?? '')) ?>"
                                    value="<?= e((string) ($field['value'] ?? '')) ?>"
                                    type="<?= e($inputType) ?>"
                                >
                            <?php endif; ?>
                            <?php if (!empty($field['description'])): ?>
                                <p class="mt-1 text-xs text-slate-500"><?= e((string) $field['description']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-slate-500">Default: <?= e($defaultLabel) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <div>
            <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Save Settings</button>
        </div>
    </form>
</section>
