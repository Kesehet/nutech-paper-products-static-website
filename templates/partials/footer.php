<?php
declare(strict_types=1);

$siteTitle = (string) ($site['title'] ?? 'Nuteck Paper Products');
$footerTagline = trim((string) ($site['footer_tagline'] ?? ''));
if ($footerTagline === '') {
    $footerTagline = 'Trusted partner for high-performance self-adhesive and release paper solutions.';
}
$footerNav = $footerNav ?? $primaryNav ?? [];
$logoPath = trim((string) ($site['logo_path'] ?? '/assets/img/nuteck_square_logo.png'));
if ($logoPath === '') {
    $logoPath = '/assets/img/nuteck_square_logo.png';
}
$logoUrl = preg_match('#^(https?:)?//#i', $logoPath) === 1
    ? $logoPath
    : ($logoPath[0] === '/' ? path_url($logoPath) : asset($logoPath));
$socialLinks = array_values(array_filter([
    [
        'label' => 'LinkedIn',
        'href' => trim((string) ($site['social_linkedin'] ?? '')),
        'icon' => 'linkedin',
    ],
    [
        'label' => 'Facebook',
        'href' => trim((string) ($site['social_facebook'] ?? '')),
        'icon' => 'facebook',
    ],
    [
        'label' => 'Instagram',
        'href' => trim((string) ($site['social_instagram'] ?? '')),
        'icon' => 'instagram',
    ],
], static fn (array $item): bool => $item['href'] !== ''));
$renderSocialIcon = static function (string $network): string {
    return match ($network) {
        'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-4 w-4"><path d="M6.94 8.5H3.56V20h3.38V8.5ZM5.25 3C4.17 3 3.3 3.9 3.3 5s.87 2 1.95 2 1.95-.9 1.95-2S6.33 3 5.25 3Zm6.97 5.5H8.9V20h3.32v-6.03c0-1.59.3-3.13 2.25-3.13 1.93 0 1.95 1.8 1.95 3.23V20h3.33v-6.61c0-3.25-.7-5.74-4.5-5.74-1.82 0-3.04 1-3.53 1.95h-.05V8.5Z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-4 w-4"><path d="M13.5 21v-7.03h2.36l.35-2.74H13.5V9.48c0-.8.22-1.34 1.36-1.34h1.45V5.69c-.25-.03-1.1-.11-2.09-.11-2.07 0-3.48 1.27-3.48 3.58v2.07H8.4v2.74h2.34V21h2.76Z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-4 w-4"><path d="M7.75 3h8.5A4.75 4.75 0 0 1 21 7.75v8.5A4.75 4.75 0 0 1 16.25 21h-8.5A4.75 4.75 0 0 1 3 16.25v-8.5A4.75 4.75 0 0 1 7.75 3Zm0 1.8A2.95 2.95 0 0 0 4.8 7.75v8.5a2.95 2.95 0 0 0 2.95 2.95h8.5a2.95 2.95 0 0 0 2.95-2.95v-8.5a2.95 2.95 0 0 0-2.95-2.95h-8.5Zm8.9 1.35a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 7.6A4.4 4.4 0 1 1 7.6 12 4.4 4.4 0 0 1 12 7.6Zm0 1.8A2.6 2.6 0 1 0 14.6 12 2.6 2.6 0 0 0 12 9.4Z"/></svg>',
        default => '',
    };
};
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
                    <?= e($footerTagline) ?>
                </p>
                <?php if ($socialLinks !== []): ?>
                    <div class="mt-5 flex items-center gap-3" aria-label="Social media links">
                        <?php foreach ($socialLinks as $social): ?>
                            <a
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900/40 text-slate-300 transition-colors hover:border-primary hover:text-primary"
                                href="<?= e($social['href']) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="<?= e($social['label']) ?>"
                                title="<?= e($social['label']) ?>"
                            >
                                <?= $renderSocialIcon((string) $social['icon']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                <p class="text-sm text-slate-400"><?= e((string) ($site['contact_email'] ?? 'info@nuteckpaper.com')) ?></p>
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
