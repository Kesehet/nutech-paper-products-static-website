<?php
declare(strict_types=1);
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-dark-navy">Customer Inquiries</h2>
            <p class="text-sm text-slate-600 mt-1">Admin-only access to public contact submissions.</p>
        </div>
        <form method="post" action="<?= e(path_url('/admin/inquiries/export')) ?>">
            <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
            <input type="hidden" name="q" value="<?= e((string) ($search ?? '')) ?>">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-dark-navy rounded-lg text-sm font-bold">
                Export CSV
            </button>
        </form>
    </div>

    <form class="mb-6" method="get" action="<?= e(path_url('/admin/inquiries')) ?>">
        <input class="w-full md:w-96 rounded-xl border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary" type="search" name="q" placeholder="Search by name, email, company, message, product..." value="<?= e((string) ($search ?? '')) ?>">
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="py-3 pr-4">Customer</th>
                    <th class="py-3 pr-4">Inquiry</th>
                    <th class="py-3 pr-4">Product</th>
                    <th class="py-3 pr-4">Source</th>
                    <th class="py-3 pr-4">Message</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3">Submitted</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($inquiries)): ?>
                <?php foreach ($inquiries as $inquiry): ?>
                    <?php
                    $message = trim((string) ($inquiry['message'] ?? ''));
                    $messagePreview = $message;
                    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                        if (mb_strlen($messagePreview) > 140) {
                            $messagePreview = mb_substr($messagePreview, 0, 140) . '...';
                        }
                    } elseif (strlen($messagePreview) > 140) {
                        $messagePreview = substr($messagePreview, 0, 140) . '...';
                    }
                    ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="py-3 pr-4">
                            <p class="font-semibold text-slate-800"><?= e((string) ($inquiry['full_name'] ?? '')) ?></p>
                            <p class="text-slate-600"><?= e((string) ($inquiry['email'] ?? '')) ?></p>
                            <?php if (!empty($inquiry['phone'])): ?>
                            <p class="text-slate-500"><?= e((string) $inquiry['phone']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($inquiry['company_name'])): ?>
                            <p class="text-slate-500"><?= e((string) $inquiry['company_name']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 pr-4 text-slate-700"><?= e((string) ($inquiry['inquiry_type'] ?? 'General Inquiry')) ?></td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($inquiry['product_title'] ?? '-')) ?></td>
                        <td class="py-3 pr-4 text-slate-600"><?= e((string) ($inquiry['source_page'] ?? '-')) ?></td>
                        <td class="py-3 pr-4 text-slate-600 max-w-sm"><?= e($messagePreview) ?></td>
                        <td class="py-3 pr-4">
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                <?= e(ucfirst((string) ($inquiry['status'] ?? 'new'))) ?>
                            </span>
                        </td>
                        <td class="py-3 text-slate-600 whitespace-nowrap"><?= e((string) ($inquiry['created_at'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">No inquiries found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    $basePath = '/admin/inquiries';
    $query = ['q' => (string) ($search ?? '')];
    $pageKey = 'page';
    require BASE_PATH . '/templates/admin/partials/pagination.php';
    ?>
</section>
