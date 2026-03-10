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
</section>

