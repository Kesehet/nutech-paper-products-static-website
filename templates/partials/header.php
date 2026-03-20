<?php
declare(strict_types=1);

$currentPath = (string) ($currentPath ?? '/');
$primaryNav = $primaryNav ?? [];
$siteTitle = (string) ($site['title'] ?? 'Nuteck Paper Products');
$logoPath = trim((string) ($site['logo_path'] ?? '/assets/img/nutech_square_logo.png'));
if ($logoPath === '') {
    $logoPath = '/assets/img/nutech_square_logo.png';
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
<header class="sticky top-0 z-50 w-full border-b border-slate-700/50 bg-dark-navy backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a href="<?= e(path_url('/')) ?>" class="flex items-center gap-3">
            <img src="<?= e($logoUrl) ?>" alt="<?= e($siteTitle) ?>" class="h-10 w-auto">
            <span class="text-xl font-bold tracking-tight text-white hidden sm:block"><?= e($siteTitle) ?></span>
        </a>
        <button id="mobile-nav-toggle" class="md:hidden text-white" type="button" aria-label="Toggle menu">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <nav id="desktop-nav" class="hidden md:flex items-center gap-8">
            <?php foreach ($primaryNav as $item): ?>
                <?php
                $href = (string) ($item['href'] ?? '#');
                $isActive = $currentPath === rtrim($href, '/') || ($href === '/' && $currentPath === '/');
                ?>
                <a class="text-sm font-medium transition-colors <?= $isActive ? 'text-primary' : 'text-slate-300 hover:text-primary' ?>" href="<?= e(path_url($href)) ?>">
                    <?= e((string) ($item['label'] ?? '')) ?>
                </a>
            <?php endforeach; ?>
            <a class="bg-primary hover:bg-primary-hover text-dark-navy px-5 py-2 rounded-lg text-sm font-bold transition-all" href="<?= e(path_url('/contact-us')) ?>">
                Get a Quote
            </a>
            <?php if ($socialLinks !== []): ?>
                <div class="hidden lg:flex items-center gap-2 border-l border-slate-700/70 pl-4" aria-label="Social media links">
                    <?php foreach ($socialLinks as $social): ?>
                        <a
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 text-slate-300 transition-colors hover:border-primary hover:text-primary"
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
        </nav>
    </div>
    <nav id="mobile-nav" class="md:hidden hidden border-t border-slate-700/50 bg-dark-navy px-4 py-4">
        <div class="flex flex-col gap-3">
            <?php foreach ($primaryNav as $item): ?>
                <a class="text-sm font-medium text-slate-300 hover:text-primary transition-colors" href="<?= e(path_url((string) ($item['href'] ?? '#'))) ?>">
                    <?= e((string) ($item['label'] ?? '')) ?>
                </a>
            <?php endforeach; ?>
            <a class="bg-primary text-dark-navy px-4 py-2 rounded-lg text-sm font-bold w-fit" href="<?= e(path_url('/contact-us')) ?>">Get a Quote</a>
            <?php if ($socialLinks !== []): ?>
                <div class="flex items-center gap-2 pt-2" aria-label="Social media links">
                    <?php foreach ($socialLinks as $social): ?>
                        <a
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 text-slate-300 transition-colors hover:border-primary hover:text-primary"
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
    </nav>
</header>
