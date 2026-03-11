<?php
declare(strict_types=1);

$sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];

$getSectionContent = static function (string $sectionKey, array $defaults = []) use ($sections): array {
    $content = $sections[$sectionKey]['content'] ?? [];
    if (!is_array($content)) {
        $content = [];
    }
    return array_replace($defaults, $content);
};

$isSectionVisible = static function (string $sectionKey) use ($sections): bool {
    return (bool) ($sections[$sectionKey]['is_visible'] ?? true);
};

$intro = $getSectionContent('contact.intro', [
    'heading' => 'Let us start a conversation',
    'description' => 'Have questions about our premium paper products? Our team is ready to help.',
]);

$form = $getSectionContent('contact.form', [
    'heading' => 'Get in Touch',
    'full_name_label' => 'Full Name *',
    'email_label' => 'Email *',
    'phone_label' => 'Phone',
    'company_label' => 'Company',
    'inquiry_label' => 'Inquiry Type',
    'message_label' => 'Message *',
    'submit_label' => 'Submit Inquiry',
    'option_1' => 'Product Inquiry',
    'option_2' => 'Bulk Order',
    'option_3' => 'Technical Support',
    'option_4' => 'General Inquiry',
]);

$sidebar = $getSectionContent('contact.sidebar', [
    'details_heading' => 'Contact Details',
    'urgent_heading' => 'Need urgent assistance?',
    'urgent_description' => 'Our team usually responds within one business day.',
    'urgent_button_label' => 'Call Now',
]);

$inquiryOptions = [];
for ($i = 1; $i <= 8; $i++) {
    $option = trim((string) ($form['option_' . $i] ?? ''));
    if ($option !== '') {
        $inquiryOptions[] = $option;
    }
}

if ($inquiryOptions === []) {
    $inquiryOptions = ['Product Inquiry', 'Bulk Order', 'Technical Support', 'General Inquiry'];
}

$showForm = $isSectionVisible('contact.form');
$showSidebar = $isSectionVisible('contact.sidebar');
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
    <?php if ($isSectionVisible('contact.intro')): ?>
    <div class="mb-12">
        <h1 class="text-4xl font-black text-dark-navy mb-4"><?= e((string) ($intro['heading'] ?? '')) ?></h1>
        <p class="text-lg text-slate-600 max-w-2xl"><?= e((string) ($intro['description'] ?? '')) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <div class="<?= ($showForm && $showSidebar) ? 'grid lg:grid-cols-5 gap-8' : 'space-y-8' ?>">
        <?php if ($showForm): ?>
        <div class="<?= $showSidebar ? 'lg:col-span-3' : '' ?> bg-white rounded-2xl border border-slate-200 p-8">
            <h2 class="text-2xl font-bold text-dark-navy mb-6"><?= e((string) ($form['heading'] ?? 'Get in Touch')) ?></h2>
            <form action="<?= e(path_url('/contact-us')) ?>" method="post" class="grid md:grid-cols-2 gap-4">
                <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['full_name_label'] ?? 'Full Name *')) ?></label>
                    <input class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" type="text" name="full_name" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['email_label'] ?? 'Email *')) ?></label>
                    <input class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" type="email" name="email" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['phone_label'] ?? 'Phone')) ?></label>
                    <input class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" type="text" name="phone">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['company_label'] ?? 'Company')) ?></label>
                    <input class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" type="text" name="company_name">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['inquiry_label'] ?? 'Inquiry Type')) ?></label>
                    <select class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" name="inquiry_type">
                        <?php foreach ($inquiryOptions as $option): ?>
                        <option><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($form['message_label'] ?? 'Message *')) ?></label>
                    <textarea class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" rows="5" name="message" required></textarea>
                </div>
                <div class="md:col-span-2">
                    <button class="bg-primary hover:bg-primary-hover text-dark-navy px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary/20" type="submit">
                        <?= e((string) ($form['submit_label'] ?? 'Submit Inquiry')) ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($showSidebar): ?>
        <aside class="<?= $showForm ? 'lg:col-span-2' : '' ?> space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-dark-navy mb-4"><?= e((string) ($sidebar['details_heading'] ?? 'Contact Details')) ?></h3>
                <p class="text-slate-600 text-sm mb-2"><?= e((string) ($site['address'] ?? '')) ?></p>
                <p class="text-slate-600 text-sm mb-2"><?= e((string) ($site['contact_phone'] ?? '')) ?></p>
                <p class="text-slate-600 text-sm"><?= e((string) ($site['contact_email'] ?? '')) ?></p>
            </div>
            <div class="bg-dark-navy text-white rounded-2xl p-6">
                <h3 class="text-xl font-bold mb-3"><?= e((string) ($sidebar['urgent_heading'] ?? 'Need urgent assistance?')) ?></h3>
                <p class="text-slate-300 text-sm mb-4"><?= e((string) ($sidebar['urgent_description'] ?? 'Our team usually responds within one business day.')) ?></p>
                <a class="inline-block bg-primary text-dark-navy px-4 py-2 rounded-lg text-sm font-bold" href="tel:<?= e((string) preg_replace('/\s+/', '', (string) ($site['contact_phone'] ?? ''))) ?>">
                    <?= e((string) ($sidebar['urgent_button_label'] ?? 'Call Now')) ?>
                </a>
            </div>
        </aside>
        <?php endif; ?>
    </div>
</section>
