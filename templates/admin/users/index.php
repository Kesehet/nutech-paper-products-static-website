<?php
declare(strict_types=1);
?>
<section class="space-y-6">
    <form action="<?= e(path_url('/admin/users/store')) ?>" method="post" class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <h2 class="text-2xl font-black text-dark-navy mb-2">Create User</h2>
        <p class="text-sm text-slate-600 mb-5">Add Admin or Content Editor accounts.</p>
        <div class="grid md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Full Name</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="full_name" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="email" name="email" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="password" name="password" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Role</label>
                <select class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" name="role_id" required>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e((string) ($role['id'] ?? '')) ?>"><?= e((string) ($role['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <label class="inline-flex items-center gap-2 text-sm mt-4">
            <input type="checkbox" name="is_active" value="1" checked>
            Active
        </label>
        <div class="mt-4">
            <button class="px-5 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors" type="submit">Create User</button>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
        <h3 class="text-xl font-black text-dark-navy mb-4">Existing Users</h3>
        <form class="mb-4" method="get" action="<?= e(path_url('/admin/users')) ?>">
            <input class="w-full md:w-96 rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="search" name="q" placeholder="Search users..." value="<?= e((string) ($search ?? '')) ?>">
        </form>
        <div class="space-y-4">
            <?php foreach ($users as $userRow): ?>
                <div class="rounded-xl border border-slate-200 p-4">
                    <form action="<?= e(path_url('/admin/users/' . (string) ($userRow['id'] ?? 0) . '/update')) ?>" method="post">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                        <div class="grid md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Full Name</label>
                                <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="full_name" value="<?= e((string) ($userRow['full_name'] ?? '')) ?>">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Email</label>
                                <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="email" value="<?= e((string) ($userRow['email'] ?? '')) ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Role</label>
                                <select class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="role_id">
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= e((string) ($role['id'] ?? '')) ?>" <?= ((string) ($userRow['role_id'] ?? '') === (string) ($role['id'] ?? '')) ? 'selected' : '' ?>>
                                            <?= e((string) ($role['name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Active</label>
                                <select class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" name="is_active">
                                    <option value="1" <?= ((int) ($userRow['is_active'] ?? 0) === 1) ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= ((int) ($userRow['is_active'] ?? 0) === 0) ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-3 mt-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">New Password (optional)</label>
                                <input class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm" type="password" name="password" placeholder="Leave blank to keep current password">
                            </div>
                            <div class="flex items-end gap-3">
                                <button class="px-4 py-2 bg-primary text-dark-navy rounded-lg text-sm font-semibold" type="submit">Save</button>
                            </div>
                        </div>
                    </form>
                    <form class="mt-3" method="post" action="<?= e(path_url('/admin/users/' . (string) ($userRow['id'] ?? 0) . '/delete')) ?>" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                        <button class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold" type="submit">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        $basePath = '/admin/users';
        $query = ['q' => (string) ($search ?? '')];
        $pageKey = 'page';
        require BASE_PATH . '/templates/admin/partials/pagination.php';
        ?>
    </div>
</section>
