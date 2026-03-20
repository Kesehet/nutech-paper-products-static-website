<?php
declare(strict_types=1);

$siteTitle = (string) ($site['title'] ?? 'Nuteck Paper Products');
$footerNav = $footerNav ?? $primaryNav ?? [];
$logoPath = trim((string) ($site['logo_path'] ?? '/assets/img/nutech_square_logo.png'));
if ($logoPath === '') {
    $logoPath = '/assets/img/nutech_square_logo.png';
}
$logoUrl = preg_match('#^(https?:)?//#i', $logoPath) === 1
    ? $logoPath
    : ($logoPath[0] === '/' ? path_url($logoPath) : asset($logoPath));
?>
<footer class="bg-dark-navy border-t border-slate-800 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-10">
            <div>
                <a href="<?= e(path_url('/')) ?>" class="flex items-center gap-3 mb-4">
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($siteTitle) ?>" class="h-9 w-auto">
                    <span class="text-white font-bold"><?= e($siteTitle) ?></span>
                </a>
                <p class="text-sm text-slate-400">
                    Trusted partner for high-performance self-adhesive and release paper solutions.
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <?php foreach ($footerNav as $item): ?>
                    <li>
                        <a class="text-sm text-slate-400 hover:text-primary transition-colors" href="<?= e(path_url((string) ($item['href'] ?? '#'))) ?>">
                            <?= e((string) ($item['label'] ?? '')) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Products</h4>
                <ul class="space-y-2">
                    <li><a class="text-sm text-slate-400 hover:text-primary transition-colors" href="<?= e(path_url('/product/pre-gummed-paper')) ?>">Pre Gummed Paper</a></li>
                    <li><a class="text-sm text-slate-400 hover:text-primary transition-colors" href="<?= e(path_url('/product/holographic-cold-foil')) ?>">Holographic Cold Foil</a></li>
                    <li><a class="text-sm text-slate-400 hover:text-primary transition-colors" href="<?= e(path_url('/product/pressure-sensitive-paper')) ?>">Pressure Sensitive Paper</a></li>
                    <li><a class="text-sm text-slate-400 hover:text-primary transition-colors" href="<?= e(path_url('/product/cck-release-paper')) ?>">CCK Release Paper</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Contact</h4>
                <p class="text-sm text-slate-400"><?= e((string) ($site['address'] ?? 'Plot No. 45, Okhla Industrial Estate, Phase III, New Delhi - 110020, India')) ?></p>
                <p class="text-sm text-slate-400 mt-2"><?= e((string) ($site['contact_phone'] ?? '+91 11 5555 4444')) ?></p>
                <p class="text-sm text-slate-400"><?= e((string) ($site['contact_email'] ?? 'info@nutechpaper.com')) ?></p>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-800 pt-6 flex flex-col md:flex-row gap-3 justify-between">
            <p class="text-sm text-slate-500">&copy; <?= date('Y') ?> <?= e($siteTitle) ?>. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a class="text-xs text-slate-500 hover:text-primary" href="#">Privacy Policy</a>
                <a class="text-xs text-slate-500 hover:text-primary" href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
