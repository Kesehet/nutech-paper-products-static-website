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

$resolveAssetUrl = static function (string $value): string {
    $path = trim($value);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $path) === 1) {
        return $path;
    }
    return $path[0] === '/' ? path_url($path) : asset($path);
};

$hero = $getSectionContent('about.hero', [
    'badge' => 'Precision & Quality',
    'heading' => 'Pioneering Paper Excellence Since 1995.',
    'description' => 'Nutech Paper Products is a leader in self-adhesive paper manufacturing, delivering innovative B2B solutions across global industries.',
    'image_path' => 'https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?auto=format&fit=crop&w=1600&q=80',
    'image_alt' => 'Industrial paper manufacturing facility with large machinery',
]);

$story = $getSectionContent('about.story', [
    'heading' => 'Our Story',
    'description_1' => 'Established in New Delhi in 1995, Nutech has been at the forefront of the paper industry for over two decades. What began as a local vision has grown into a national powerhouse in paper processing.',
    'description_2' => 'Nutech Paper Products began its journey with a vision to provide high-quality paper solutions. Over the years, we have grown into a leading manufacturer, known for our reliability and innovation in the B2B sector.',
    'years_value' => '28+',
    'years_label' => 'Years of Experience',
    'clients_value' => '1500+',
    'clients_label' => 'Clients Served',
    'image_path' => 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=1200&q=80',
    'image_alt' => 'Close up of high quality adhesive paper rolls',
]);

$expertise = $getSectionContent('about.expertise', [
    'heading' => 'Our Expertise',
    'description' => 'Specialized manufacturing of self-adhesive papers and specialized films tailored for industrial requirements.',
    'item_1_icon' => 'precision_manufacturing',
    'item_1_title' => 'Self-Adhesive Solutions',
    'item_1_description' => 'Premium quality Chromo, Mirror Coat, and Woodfree self-adhesive papers for various surfaces.',
    'item_2_icon' => 'layers',
    'item_2_title' => 'Specialized Films',
    'item_2_description' => 'BOPP, PE, and PET films designed for durability and high-performance labeling applications.',
    'item_3_icon' => 'architecture',
    'item_3_title' => 'Custom Coating',
    'item_3_description' => 'Advanced siliconizing and adhesive coating techniques tailored to specific B2B needs.',
]);

$industries = $getSectionContent('about.industries', [
    'heading' => 'Industries Served',
    'description' => 'Our products power critical operations across diverse industrial landscapes.',
    'cta_label' => 'Explore Applications',
    'cta_link' => '/contact-us',
    'item_1_title' => 'Packaging',
    'item_1_image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=900&q=80',
    'item_1_alt' => 'Modern logistics and packaging warehouse',
    'item_2_title' => 'Printing',
    'item_2_image' => 'https://images.unsplash.com/photo-1589365278144-c9e705f843ba?auto=format&fit=crop&w=900&q=80',
    'item_2_alt' => 'Commercial printing machine',
    'item_3_title' => 'Labeling',
    'item_3_image' => 'https://images.unsplash.com/photo-1609833975787-5c143f373a97?auto=format&fit=crop&w=900&q=80',
    'item_3_alt' => 'Product containers with adhesive labels',
    'item_4_title' => 'Pharma & FMCG',
    'item_4_image' => 'https://images.unsplash.com/photo-1581091215367-59ab6dcef782?auto=format&fit=crop&w=900&q=80',
    'item_4_alt' => 'High-tech manufacturing facility interior',
]);

$quality = $getSectionContent('about.quality', [
    'heading' => 'Quality Commitment',
    'description' => "At Nutech, quality is not a department; it's our core philosophy. Every roll of paper that leaves our facility undergoes rigorous testing to ensure it meets international B2B standards. We are committed to sustainable practices and continuous innovation.",
    'bullet_1' => 'ISO Certified Production Processes',
    'bullet_2' => '100% In-house Quality Inspection',
    'bullet_3' => 'Sustainable Material Sourcing',
    'image_path' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
    'image_alt' => 'Engineer inspecting material quality in a laboratory',
]);

$expertiseCards = [];
for ($i = 1; $i <= 3; $i++) {
    $expertiseCards[] = [
        'icon' => (string) ($expertise['item_' . $i . '_icon'] ?? ''),
        'title' => (string) ($expertise['item_' . $i . '_title'] ?? ''),
        'description' => (string) ($expertise['item_' . $i . '_description'] ?? ''),
    ];
}

$industryCards = [];
for ($i = 1; $i <= 4; $i++) {
    $industryCards[] = [
        'title' => (string) ($industries['item_' . $i . '_title'] ?? ''),
        'image' => $resolveAssetUrl((string) ($industries['item_' . $i . '_image'] ?? '')),
        'alt' => (string) ($industries['item_' . $i . '_alt'] ?? ''),
    ];
}

$qualityBullets = array_values(array_filter([
    trim((string) ($quality['bullet_1'] ?? '')),
    trim((string) ($quality['bullet_2'] ?? '')),
    trim((string) ($quality['bullet_3'] ?? '')),
    trim((string) ($quality['bullet_4'] ?? '')),
], static fn (string $item): bool => $item !== ''));

$heroImage = $resolveAssetUrl((string) ($hero['image_path'] ?? ''));
$storyImage = $resolveAssetUrl((string) ($story['image_path'] ?? ''));
$qualityImage = $resolveAssetUrl((string) ($quality['image_path'] ?? ''));
?>
<?php if ($isSectionVisible('about.hero')): ?>
<section class="relative h-[500px] flex items-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-dark-navy/90 via-dark-navy/60 to-transparent z-10"></div>
        <img class="w-full h-full object-cover" src="<?= e($heroImage) ?>" alt="<?= e((string) ($hero['image_alt'] ?? 'Manufacturing Plant')) ?>">
    </div>
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="max-w-2xl">
            <span class="inline-block py-1 px-3 rounded-full bg-primary/20 text-primary text-xs font-bold uppercase tracking-widest mb-4">
                <?= e((string) ($hero['badge'] ?? 'Precision & Quality')) ?>
            </span>
            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                <?= e((string) ($hero['heading'] ?? 'Pioneering Paper Excellence Since 1995.')) ?>
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed max-w-xl">
                <?= e((string) ($hero['description'] ?? 'Nutech Paper Products is a leader in self-adhesive paper manufacturing, delivering innovative B2B solutions across global industries.')) ?>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isSectionVisible('about.story')): ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-16 items-center">
            <div class="space-y-6">
                <h2 class="text-3xl md:text-4xl font-black tracking-tight"><?= e((string) ($story['heading'] ?? 'Our Story')) ?></h2>
                <div class="h-1 w-20 bg-primary"></div>
                <p class="text-slate-600 text-lg leading-relaxed"><?= e((string) ($story['description_1'] ?? '')) ?></p>
                <p class="text-slate-600 text-lg leading-relaxed"><?= e((string) ($story['description_2'] ?? '')) ?></p>
                <div class="grid grid-cols-2 gap-8 pt-6">
                    <div>
                        <p class="text-primary text-4xl font-black"><?= e((string) ($story['years_value'] ?? '28+')) ?></p>
                        <p class="text-sm font-bold text-slate-500 uppercase"><?= e((string) ($story['years_label'] ?? 'Years of Experience')) ?></p>
                    </div>
                    <div>
                        <p class="text-primary text-4xl font-black"><?= e((string) ($story['clients_value'] ?? '1500+')) ?></p>
                        <p class="text-sm font-bold text-slate-500 uppercase"><?= e((string) ($story['clients_label'] ?? 'Clients Served')) ?></p>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="absolute -inset-4 bg-primary/10 rounded-xl -rotate-2"></div>
                <img class="relative rounded-xl shadow-2xl w-full h-[450px] object-cover" src="<?= e($storyImage) ?>" alt="<?= e((string) ($story['image_alt'] ?? 'Product Showcase')) ?>">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isSectionVisible('about.expertise')): ?>
<section class="py-20 bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4"><?= e((string) ($expertise['heading'] ?? 'Our Expertise')) ?></h2>
            <p class="text-slate-600"><?= e((string) ($expertise['description'] ?? '')) ?></p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($expertiseCards as $card): ?>
            <article class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 hover:border-primary transition-colors group">
                <div class="w-14 h-14 bg-primary/10 rounded-lg flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-all">
                    <span class="material-symbols-outlined text-3xl"><?= e((string) (($card['icon'] ?? '') !== '' ? $card['icon'] : 'check_circle')) ?></span>
                </div>
                <h3 class="text-xl font-bold mb-3"><?= e((string) ($card['title'] ?? '')) ?></h3>
                <p class="text-slate-600"><?= e((string) ($card['description'] ?? '')) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isSectionVisible('about.industries')): ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-12">
            <div class="max-w-2xl">
                <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4"><?= e((string) ($industries['heading'] ?? 'Industries Served')) ?></h2>
                <p class="text-slate-600"><?= e((string) ($industries['description'] ?? '')) ?></p>
            </div>
            <a class="text-primary font-bold flex items-center gap-2 hover:underline" href="<?= e(path_url((string) ($industries['cta_link'] ?? '/contact-us'))) ?>">
                <?= e((string) ($industries['cta_label'] ?? 'Explore Applications')) ?>
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($industryCards as $card): ?>
            <article class="relative h-64 rounded-xl overflow-hidden group">
                <?php if ((string) ($card['image'] ?? '') !== ''): ?>
                <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?= e((string) $card['image']) ?>" alt="<?= e((string) ($card['alt'] ?? 'Industry image')) ?>">
                <?php else: ?>
                <div class="w-full h-full bg-slate-200"></div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-dark-navy/80 to-transparent flex items-end p-6">
                    <span class="text-white font-bold text-lg"><?= e((string) ($card['title'] ?? 'Industry')) ?></span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isSectionVisible('about.quality')): ?>
<section class="py-20 bg-primary/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-dark-navy rounded-2xl overflow-hidden shadow-2xl flex flex-col md:flex-row">
            <div class="md:w-1/2 p-12 lg:p-16 flex flex-col justify-center">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-6"><?= e((string) ($quality['heading'] ?? 'Quality Commitment')) ?></h2>
                <p class="text-slate-300 text-lg leading-relaxed mb-8"><?= e((string) ($quality['description'] ?? '')) ?></p>
                <?php if ($qualityBullets !== []): ?>
                <ul class="space-y-4">
                    <?php foreach ($qualityBullets as $bullet): ?>
                    <li class="flex items-center gap-3 text-white">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        <span><?= e($bullet) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <div class="md:w-1/2">
                <img class="w-full h-full object-cover min-h-[400px]" src="<?= e($qualityImage) ?>" alt="<?= e((string) ($quality['image_alt'] ?? 'Quality Assurance')) ?>">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
