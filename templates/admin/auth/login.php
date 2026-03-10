<?php
declare(strict_types=1);
?>
<div class="min-h-screen grid place-items-center px-4 py-12 bg-slate-100">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
        <h1 class="text-2xl font-black text-dark-navy mb-2">Admin Login</h1>
        <p class="text-sm text-slate-600 mb-6">Sign in to manage website content and products.</p>
        <?php if (!empty($error)): ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= e((string) $error) ?></div>
        <?php endif; ?>
        <form action="<?= e(path_url('/admin/login')) ?>" method="post" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="email" name="email" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="password" name="password" required>
            </div>
            <button class="w-full py-3 bg-primary hover:bg-primary-hover text-dark-navy rounded-xl font-bold transition-colors" type="submit">
                Sign In
            </button>
        </form>
    </div>
</div>
