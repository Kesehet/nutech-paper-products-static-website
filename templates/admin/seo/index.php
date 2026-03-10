<?php
declare(strict_types=1);

$scriptByLocation = [];
foreach ($scripts as $scriptRow) {
    $scriptByLocation[(string) ($scriptRow['location'] ?? '')] = $scriptRow;
}
?>
<section class="space-y-6">
    <form action="<?= e(path_url('/admin/seo/save')) ?>" method="post" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <input type="hidden" name="entity_type" value="global">
        <input type="hidden" name="entity_id" value="0">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-2xl font-black text-dark-navy">Global SEO Defaults</h2>
                <p class="text-sm text-slate-600 mt-1">Fallback metadata used when page/product SEO is missing.</p>
            </div>
            <?php if (!$isAdmin): ?>
            <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500">Admin Only</span>
            <?php endif; ?>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Meta Title</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="meta_title" value="<?= e((string) ($globalSeo['meta_title'] ?? '')) ?>" <?= $isAdmin ? '' : 'disabled' ?>>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Canonical URL</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="canonical_url" value="<?= e((string) ($globalSeo['canonical_url'] ?? '')) ?>" <?= $isAdmin ? '' : 'disabled' ?>>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-semibold mb-2">Meta Description</label>
            <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" rows="3" name="meta_description" <?= $isAdmin ? '' : 'disabled' ?>><?= e((string) ($globalSeo['meta_description'] ?? '')) ?></textarea>
        </div>
        <div class="mt-4">
            <button class="px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors disabled:opacity-50" type="submit" <?= $isAdmin ? '' : 'disabled' ?>>Save Global SEO</button>
        </div>
    </form>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-xl font-black text-dark-navy mb-4">Page SEO</h3>
            <form class="mb-3" method="get" action="<?= e(path_url('/admin/seo')) ?>">
                <input type="hidden" name="product_q" value="<?= e((string) ($productSearch ?? '')) ?>">
                <input type="hidden" name="product_page" value="<?= e((string) ($productPagination['page'] ?? 1)) ?>">
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" type="search" name="page_q" placeholder="Search pages..." value="<?= e((string) ($pageSearch ?? '')) ?>">
            </form>
            <ul class="space-y-2">
                <?php foreach ($pages as $row): ?>
                <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 p-3">
                    <div>
                        <p class="font-semibold text-slate-800"><?= e((string) ($row['title'] ?? '')) ?></p>
                        <p class="text-xs text-slate-500">/<?= e((string) ($row['slug'] ?? '')) ?></p>
                    </div>
                    <a class="text-primary text-sm font-semibold hover:underline" href="<?= e(path_url('/admin/seo/edit/page/' . (string) ($row['id'] ?? 0))) ?>">
                        <?= ((int) ($row['has_seo'] ?? 0) === 1) ? 'Edit' : 'Add' ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            $pagination = $pagePagination ?? [];
            $basePath = '/admin/seo';
            $query = [
                'page_q' => (string) ($pageSearch ?? ''),
                'product_q' => (string) ($productSearch ?? ''),
                'product_page' => (string) ($productPagination['page'] ?? 1),
            ];
            $pageKey = 'page_page';
            require BASE_PATH . '/templates/admin/partials/pagination.php';
            ?>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-xl font-black text-dark-navy mb-4">Product SEO</h3>
            <form class="mb-3" method="get" action="<?= e(path_url('/admin/seo')) ?>">
                <input type="hidden" name="page_q" value="<?= e((string) ($pageSearch ?? '')) ?>">
                <input type="hidden" name="page_page" value="<?= e((string) ($pagePagination['page'] ?? 1)) ?>">
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" type="search" name="product_q" placeholder="Search products..." value="<?= e((string) ($productSearch ?? '')) ?>">
            </form>
            <ul class="space-y-2 max-h-96 overflow-auto pr-1">
                <?php foreach ($products as $row): ?>
                <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 p-3">
                    <div>
                        <p class="font-semibold text-slate-800"><?= e((string) ($row['title'] ?? '')) ?></p>
                        <p class="text-xs text-slate-500"><?= e((string) ($row['slug'] ?? '')) ?></p>
                    </div>
                    <a class="text-primary text-sm font-semibold hover:underline" href="<?= e(path_url('/admin/seo/edit/product/' . (string) ($row['id'] ?? 0))) ?>">
                        <?= ((int) ($row['has_seo'] ?? 0) === 1) ? 'Edit' : 'Add' ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            $pagination = $productPagination ?? [];
            $basePath = '/admin/seo';
            $query = [
                'page_q' => (string) ($pageSearch ?? ''),
                'product_q' => (string) ($productSearch ?? ''),
                'page_page' => (string) ($pagePagination['page'] ?? 1),
            ];
            $pageKey = 'product_page';
            require BASE_PATH . '/templates/admin/partials/pagination.php';
            ?>
        </div>
    </div>

    <form action="<?= e(path_url('/admin/seo/scripts/save')) ?>" method="post" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl font-black text-dark-navy">Script Injection</h3>
                <p class="text-sm text-slate-600 mt-1">Manage `head` and `footer` snippets for analytics/verification.</p>
            </div>
            <?php if (!$isAdmin): ?>
            <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500">Admin Only</span>
            <?php endif; ?>
        </div>

        <?php foreach (['head_start' => 'Head Start', 'head_end' => 'Head End', 'body_end' => 'Body End'] as $location => $label): ?>
            <?php $row = $scriptByLocation[$location] ?? []; ?>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2"><?= e($label) ?></label>
                <textarea class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary font-mono text-xs" rows="4" name="script_<?= e($location) ?>" <?= $isAdmin ? '' : 'disabled' ?>><?= e((string) ($row['script_content'] ?? '')) ?></textarea>
                <label class="inline-flex items-center gap-2 text-xs text-slate-600 mt-2">
                    <input type="checkbox" name="script_active_<?= e($location) ?>" value="1" <?= ((int) ($row['is_active'] ?? 0) === 1) ? 'checked' : '' ?> <?= $isAdmin ? '' : 'disabled' ?>>
                    Active
                </label>
            </div>
        <?php endforeach; ?>

        <button class="px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors disabled:opacity-50" type="submit" <?= $isAdmin ? '' : 'disabled' ?>>Save Script Injection</button>
    </form>
</section>
