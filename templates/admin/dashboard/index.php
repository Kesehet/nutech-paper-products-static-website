<?php
declare(strict_types=1);
?>
<section>
    <p class="text-slate-600 mb-8">Welcome back, <?= e((string) ($authUser['name'] ?? '')) ?>.</p>
    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Pages</p>
            <p class="text-3xl font-black text-dark-navy mt-2"><?= e((string) ($stats['pages'] ?? 0)) ?></p>
        </article>
        <article class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Products</p>
            <p class="text-3xl font-black text-dark-navy mt-2"><?= e((string) ($stats['products'] ?? 0)) ?></p>
        </article>
        <article class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Media Files</p>
            <p class="text-3xl font-black text-dark-navy mt-2"><?= e((string) ($stats['media'] ?? 0)) ?></p>
        </article>
        <article class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Users</p>
            <p class="text-3xl font-black text-dark-navy mt-2"><?= e((string) ($stats['users'] ?? 0)) ?></p>
        </article>
    </div>

    <div class="mt-8 bg-white border border-slate-200 rounded-2xl p-5">
        <h2 class="text-lg font-black text-dark-navy mb-4">Recent Activity</h2>
        <?php if (!empty($recentActivity)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Time</th>
                        <th class="py-2 pr-4">User</th>
                        <th class="py-2 pr-4">Action</th>
                        <th class="py-2 pr-4">Entity</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentActivity as $log): ?>
                    <tr class="border-b border-slate-100">
                        <td class="py-2 pr-4 text-slate-600"><?= e((string) ($log['created_at'] ?? '')) ?></td>
                        <td class="py-2 pr-4 text-slate-700"><?= e((string) ($log['full_name'] ?? 'System')) ?></td>
                        <td class="py-2 pr-4 font-semibold text-slate-800"><?= e((string) ($log['action'] ?? '')) ?></td>
                        <td class="py-2 pr-4 text-slate-600">
                            <?= e((string) ($log['entity_type'] ?? '')) ?>
                            <?php if (!empty($log['entity_id'])): ?>
                                #<?= e((string) $log['entity_id']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-sm text-slate-500">No activity recorded yet.</p>
        <?php endif; ?>
    </div>
</section>
