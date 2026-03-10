<?php
declare(strict_types=1);

$pagination = $pagination ?? null;
$basePath = (string) ($basePath ?? '');
$query = is_array($query ?? null) ? $query : [];
$pageKey = (string) ($pageKey ?? 'page');
if (!is_array($pagination) || ($pagination['total_pages'] ?? 1) <= 1) {
    return;
}
?>
<div class="mt-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-sm">
    <p class="text-slate-500">
        Showing page <?= e((string) ($pagination['page'] ?? 1)) ?> of <?= e((string) ($pagination['total_pages'] ?? 1)) ?>
        (<?= e((string) ($pagination['total'] ?? 0)) ?> total)
    </p>
    <div class="flex items-center gap-2">
        <?php if (!empty($pagination['has_prev'])): ?>
            <a class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" href="<?= e(query_url($basePath, array_merge($query, [$pageKey => (int) $pagination['page'] - 1]))) ?>">Prev</a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-400">Prev</span>
        <?php endif; ?>

        <span class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-semibold"><?= e((string) ($pagination['page'] ?? 1)) ?></span>

        <?php if (!empty($pagination['has_next'])): ?>
            <a class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" href="<?= e(query_url($basePath, array_merge($query, [$pageKey => (int) $pagination['page'] + 1]))) ?>">Next</a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-400">Next</span>
        <?php endif; ?>
    </div>
</div>
