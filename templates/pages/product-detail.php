<?php
declare(strict_types=1);
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php
    $gallery = is_array($product['gallery'] ?? null) ? $product['gallery'] : [];
    $mainImage = (string) ($product['featured_image_path'] ?? '');
    if ($mainImage === '' && count($gallery) > 0) {
        $mainImage = (string) ($gallery[0]['storage_path'] ?? '');
    }
    ?>
    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
        <a class="hover:text-primary" href="<?= e(path_url('/')) ?>">Home</a>
        <span>/</span>
        <a class="hover:text-primary" href="<?= e(path_url('/product-catalog')) ?>">Product Catalog</a>
        <span>/</span>
        <span class="text-slate-700"><?= e((string) ($product['title'] ?? 'Product')) ?></span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-10 items-start">
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
            <?php if ($mainImage !== ''): ?>
                <img src="<?= e(path_url($mainImage)) ?>" alt="<?= e((string) ($product['title'] ?? 'Product')) ?>" class="w-full h-[420px] object-cover">
            <?php else: ?>
                <div class="h-[420px] bg-slate-100"></div>
            <?php endif; ?>
        </div>
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider mb-4">
                <?= e((string) ($product['category_name'] ?? 'Industrial Grade')) ?>
            </span>
            <h1 class="text-4xl font-extrabold text-slate-900 mb-4 leading-tight"><?= e((string) ($product['title'] ?? '')) ?></h1>
            <p class="text-slate-600 mb-8"><?= e((string) ($product['short_description'] ?? '')) ?></p>
            <a href="<?= e(query_url('/contact-us', ['product' => (string) ($product['slug'] ?? '')])) ?>" class="inline-flex items-center justify-center px-6 py-3 bg-primary text-dark-navy rounded-xl font-bold hover:bg-primary-hover transition-colors">
                Request a Quote
            </a>
        </div>
    </div>
    <?php if (!empty($gallery)): ?>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
        <?php foreach ($gallery as $image): ?>
        <div class="rounded-lg overflow-hidden border border-slate-200 bg-white">
            <img src="<?= e(path_url((string) ($image['storage_path'] ?? ''))) ?>" alt="<?= e((string) ($image['alt_text'] ?? ($product['title'] ?? 'Product image'))) ?>" class="w-full h-24 object-cover">
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="mt-14 grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-10">
            <section>
                <h2 class="text-2xl font-bold mb-4 text-dark-navy">Product Overview</h2>
                <p class="text-slate-600 leading-relaxed"><?= e((string) ($product['long_description'] ?? 'High-performance product designed for consistent release and industrial converting applications.')) ?></p>
            </section>

            <section>
                <h3 class="text-2xl font-bold mb-6 text-dark-navy">Technical Specifications</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <?php foreach (($product['specifications'] ?? []) as $label => $value): ?>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-xs uppercase tracking-wider text-slate-500"><?= e((string) $label) ?></p>
                        <p class="font-semibold text-slate-900"><?= e((string) $value) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section>
                <h3 class="text-2xl font-bold mb-6 text-dark-navy">Key Features</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <?php foreach (($product['features'] ?? []) as $feature): ?>
                    <div class="flex gap-3 p-4 rounded-xl bg-primary/5 border border-primary/10">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        <p class="text-slate-700"><?= e((string) $feature) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-dark-navy mb-4">Quick Inquiry</h3>
            <p class="text-sm text-slate-500 mb-6">Interested in this product? Fill out the form and our team will contact you.</p>
            <form action="<?= e(path_url('/contact-us')) ?>" method="post" class="space-y-4">
                <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
                <input type="hidden" name="contact_started_at" value="<?= e((string) time()) ?>">
                <input type="hidden" name="source_page" value="<?= e((string) ($currentPath ?? '/contact-us')) ?>">
                <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
                <div class="hidden" aria-hidden="true">
                    <label>Website</label>
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                <input class="w-full rounded-lg border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" type="text" name="full_name" placeholder="Your Name" required>
                <input class="w-full rounded-lg border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" type="email" name="email" placeholder="email@company.com" required>
                <textarea class="w-full rounded-lg border-slate-200 bg-slate-50 focus:ring-primary focus:border-primary text-sm" rows="4" name="message" placeholder="Tell us about your specific needs..." required></textarea>
                <input type="hidden" name="inquiry_type" value="Product Inquiry">
                <button class="w-full py-3 bg-primary text-dark-navy rounded-lg font-bold hover:bg-primary-hover transition-colors" type="submit">Send Inquiry</button>
            </form>
        </aside>
    </div>
</section>

<?php if (!empty($relatedProducts)): ?>
<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h3 class="text-3xl font-bold mb-8 text-dark-navy">Related Release Solutions</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($relatedProducts as $item): ?>
            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <?php if (!empty($item['featured_image_path'])): ?>
                    <img src="<?= e(path_url((string) $item['featured_image_path'])) ?>" alt="<?= e((string) ($item['title'] ?? '')) ?>" class="h-32 w-full object-cover">
                <?php else: ?>
                    <div class="h-32 bg-slate-100"></div>
                <?php endif; ?>
                <div class="p-5">
                    <h4 class="font-bold text-slate-900 mb-2"><?= e((string) ($item['title'] ?? '')) ?></h4>
                    <a class="text-sm font-semibold text-primary hover:underline" href="<?= e(path_url('/product/' . (string) ($item['slug'] ?? ''))) ?>">View Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
