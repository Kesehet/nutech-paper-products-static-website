<?php
declare(strict_types=1);

use App\Core\Auth;

$isLoggedIn = Auth::check();
$authUser = $authUser ?? Auth::user();
$meta = $meta ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<?php require BASE_PATH . '/templates/partials/head.php'; ?>
<body class="bg-slate-100 text-slate-900 font-display antialiased">
<?php if ($isLoggedIn): ?>
<div class="min-h-screen flex">
    <aside class="w-72 bg-dark-navy text-slate-100 p-6 hidden lg:block">
        <a class="text-xl font-bold tracking-tight" href="<?= e(path_url('/admin/dashboard')) ?>">Nutech CMS</a>
        <p class="text-xs text-slate-400 mt-1">Role: <?= e((string) ($authUser['role'] ?? '')) ?></p>
        <nav class="mt-8 space-y-2 text-sm">
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/dashboard')) ?>">Dashboard</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/pages')) ?>">Pages</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/products')) ?>">Products</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/categories')) ?>">Categories</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/media')) ?>">Media</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/seo')) ?>">SEO</a>
            <?php if (($authUser['role'] ?? '') === 'admin'): ?>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/settings')) ?>">Settings</a>
            <a class="block px-3 py-2 rounded hover:bg-slate-700" href="<?= e(path_url('/admin/users')) ?>">Users</a>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="flex-1 min-w-0">
        <header class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg md:text-xl font-bold"><?= e((string) ($meta['title'] ?? 'Admin')) ?></h1>
            <div class="flex items-center gap-4">
                <span class="hidden md:inline text-sm text-slate-600"><?= e((string) ($authUser['name'] ?? '')) ?></span>
                <form action="<?= e(path_url('/admin/logout')) ?>" method="post">
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
                    <button class="px-4 py-2 bg-primary text-dark-navy text-sm font-bold rounded-lg" type="submit">Logout</button>
                </form>
            </div>
        </header>
        <div class="p-4 md:p-8">
            <?php if (!empty($flashSuccess)): ?>
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <?= e((string) $flashSuccess) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?= e((string) $flashError) ?>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php else: ?>
<?= $content ?>
<?php endif; ?>
<?php require BASE_PATH . '/templates/partials/scripts.php'; ?>
</body>
</html>
