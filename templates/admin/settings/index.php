<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <h2 class="text-2xl font-black text-dark-navy mb-2">Site Settings</h2>
    <p class="text-sm text-slate-600 mb-6">Manage site identity, contact details, social links, and theme colors.</p>

    <form action="<?= e(path_url('/admin/settings/save')) ?>" method="post" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">

        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($fields as $flatKey => $type): ?>
                <?php $inputName = str_replace('.', '__', (string) $flatKey); ?>
                <div>
                    <label class="block text-sm font-semibold mb-2"><?= e(ucwords(str_replace(['.', '_'], [' ', ' '], (string) $flatKey))) ?></label>
                    <input
                        class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary"
                        name="<?= e($inputName) ?>"
                        value="<?= e((string) ($values[$flatKey] ?? '')) ?>"
                        <?= str_contains((string) $flatKey, 'color') ? 'type="color"' : 'type="text"' ?>
                    >
                </div>
            <?php endforeach; ?>
        </div>

        <div>
            <button class="px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Save Settings</button>
        </div>
    </form>
</section>

