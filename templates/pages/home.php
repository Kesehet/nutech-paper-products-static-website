<?php
declare(strict_types=1);

$hero = $page['sections']['home.hero']['content'] ?? [];
$whyChooseUs = $page['sections']['home.why_choose_us'] ?? [];
$whyChooseUsContent = is_array($whyChooseUs['content'] ?? null) ? $whyChooseUs['content'] : [];
$whyChooseUsVisible = (bool) ($whyChooseUs['is_visible'] ?? true);
$whyChooseUsItems = [];
for ($i = 1; $i <= 4; $i++) {
    $title = trim((string) ($whyChooseUsContent['item_' . $i . '_title'] ?? ''));
    $description = trim((string) ($whyChooseUsContent['item_' . $i . '_description'] ?? ''));
    if ($title === '' && $description === '') {
        continue;
    }

    $whyChooseUsItems[] = [
        'title' => $title,
        'description' => $description,
    ];
}
$homeCta = $page['sections']['home.cta'] ?? [];
$homeCtaContent = is_array($homeCta['content'] ?? null) ? $homeCta['content'] : [];
$homeCtaVisible = (bool) ($homeCta['is_visible'] ?? true);
$heroImagePath = trim((string) ($site['home_hero_image'] ?? ''));
if ($heroImagePath === '') {
    $heroImagePath = 'https://images.unsplash.com/photo-1603484477859-abe6a73f9366?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
}
$heroImageAlt = trim((string) ($site['home_hero_image_alt'] ?? ''));
if ($heroImageAlt === '') {
    $heroImageAlt = 'Nuteck manufacturing';
}
$heroImageUrl = preg_match('#^(https?:)?//#i', $heroImagePath) === 1
    ? $heroImagePath
    : ($heroImagePath[0] === '/' ? path_url($heroImagePath) : asset($heroImagePath));
?>
<section class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-primary mb-4"><?= e((string) ($hero['eyebrow'] ?? 'Industrial Excellence Since 1995')) ?></p>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-dark-navy leading-[1.1] mb-6">
                <?= e((string) ($hero['heading'] ?? 'Trusted Manufacturer of Self Adhesive and Release Papers')) ?>
            </h1>
            <p class="text-lg text-slate-600 mb-8 max-w-xl">
                <?= e((string) ($hero['description'] ?? 'High-performance paper products for packaging, converting, and labeling industries.')) ?>
            </p>
            <a class="inline-block bg-primary text-dark-navy px-8 py-4 rounded-xl font-bold text-base hover:bg-primary-hover transition-all" href="<?= e(path_url((string) ($hero['primary_cta_link'] ?? '/product-catalog'))) ?>">
                <?= e((string) ($hero['primary_cta_label'] ?? 'View Products')) ?>
            </a>
        </div>
        <div class="relative">
            <img alt="<?= e($heroImageAlt) ?>" class="rounded-2xl shadow-2xl w-full h-[420px] object-cover" src="<?= e($heroImageUrl) ?>">
            <div class="absolute -bottom-6 -left-6 bg-white rounded-xl shadow-lg p-4 border border-slate-200">
                <p class="text-xs uppercase text-slate-500 tracking-wider">Production Capacity</p>
                <p class="text-2xl font-black text-dark-navy">1500+ Tons/Year</p>
            </div>
        </div>
    </div>
</section>

<?php if ($whyChooseUsVisible && $whyChooseUsItems !== []): ?>
<section class="py-20 bg-slate-100 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-dark-navy mb-4"><?= e((string) ($whyChooseUsContent['heading'] ?? 'Why Choose Us')) ?></h2>
        <p class="text-slate-600 mb-12"><?= e((string) ($whyChooseUsContent['description'] ?? 'Trusted partner for consistent quality, timely bulk supply, and application-driven engineering.')) ?></p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($whyChooseUsItems as $item): ?>
                <div class="bg-white p-8 rounded-2xl border border-slate-200">
                    <h3 class="text-xl font-bold text-slate-900 mb-2"><?= e((string) ($item['title'] ?? '')) ?></h3>
                    <p class="text-sm text-slate-600"><?= e((string) ($item['description'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-20 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-3xl font-bold text-dark-navy mb-2">Our Product Categories</h2>
                <p class="text-slate-600">Explore our core products for labeling and release applications.</p>
            </div>
            <a class="text-primary font-semibold hover:underline" href="<?= e(path_url('/product-catalog')) ?>">Explore All Products</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            <?php foreach ($products as $product): ?>
            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <?php if (!empty($product['featured_image_path'])): ?>
                    <img src="<?= e(path_url((string) $product['featured_image_path'])) ?>" alt="<?= e((string) ($product['title'] ?? '')) ?>" class="h-36 w-full object-cover">
                <?php else: ?>
                    <div class="h-36 bg-slate-100"></div>
                <?php endif; ?>
                <div class="p-5">
                    <h3 class="text-base font-bold text-slate-900 mb-2"><?= e((string) ($product['title'] ?? '')) ?></h3>
                    <p class="text-sm text-slate-600 mb-4"><?= e((string) ($product['short_description'] ?? '')) ?></p>
                    <a class="text-sm font-semibold text-primary hover:underline" href="<?= e(path_url('/product/' . (string) ($product['slug'] ?? ''))) ?>">View Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($homeCtaVisible): ?>
<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-slate-100">
            <div class="lg:w-1/2 p-8 lg:p-16 bg-dark-navy text-white flex flex-col justify-center">
                <h2 class="text-4xl font-bold mb-6"><?= e((string) ($homeCtaContent['heading'] ?? 'Send an Enquiry')) ?></h2>
                <p class="text-slate-300 text-lg mb-8"><?= e((string) ($homeCtaContent['description'] ?? 'Have a specific requirement or looking for a bulk quote? Our experts are ready to assist you with the right paper solutions.')) ?></p>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="material-symbols-outlined">phone_in_talk</span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 mb-1"><?= e((string) ($homeCtaContent['phone_label'] ?? 'Phone')) ?></p>
                            <span class="font-medium"><?= e((string) ($site['contact_phone'] ?? '+91-11-XXXX-XXXX')) ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="material-symbols-outlined">mail</span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 mb-1"><?= e((string) ($homeCtaContent['email_label'] ?? 'Email')) ?></p>
                            <span class="font-medium"><?= e((string) ($site['contact_email'] ?? 'sales@nutechpaper.com')) ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="material-symbols-outlined">location_on</span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 mb-1"><?= e((string) ($homeCtaContent['address_label'] ?? 'Address')) ?></p>
                            <span class="font-medium"><?= e((string) ($site['address'] ?? 'New Delhi Industrial Area, India')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:w-1/2 p-8 lg:p-16">
            <form action="<?= e(path_url('/contact-us')) ?>" method="post" class="space-y-6">
                <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($homeCtaContent['name_label'] ?? 'Name')) ?></label>
                        <input class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-primary focus:border-primary transition-all" type="text" name="full_name" placeholder="<?= e((string) ($homeCtaContent['name_placeholder'] ?? 'Full Name')) ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($homeCtaContent['email_form_label'] ?? 'Email')) ?></label>
                        <input class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-primary focus:border-primary transition-all" type="email" name="email" placeholder="<?= e((string) ($homeCtaContent['email_placeholder'] ?? 'email@example.com')) ?>" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($homeCtaContent['product_interest_label'] ?? 'Product Interest')) ?></label>
                    <select class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-primary focus:border-primary transition-all" name="inquiry_type">
                        <option value="General Inquiry"><?= e((string) ($homeCtaContent['product_interest_placeholder'] ?? 'Select a product')) ?></option>
                        <?php foreach ($products as $product): ?>
                            <?php $productTitle = trim((string) ($product['title'] ?? '')); ?>
                            <?php if ($productTitle === ''): continue; endif; ?>
                            <option value="<?= e($productTitle) ?>"><?= e($productTitle) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e((string) ($homeCtaContent['message_label'] ?? 'Message')) ?></label>
                    <textarea class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-primary focus:border-primary transition-all" name="message" rows="4" placeholder="<?= e((string) ($homeCtaContent['message_placeholder'] ?? 'Your requirements...')) ?>" required></textarea>
                </div>
                <button class="w-full py-4 bg-primary hover:bg-primary-hover text-dark-navy font-bold rounded-xl transition-all shadow-lg shadow-primary/20" type="submit"><?= e((string) ($homeCtaContent['submit_label'] ?? 'Send Enquiry')) ?></button>
            </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
