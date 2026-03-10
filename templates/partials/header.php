<?php
declare(strict_types=1);

$currentPath = (string) ($currentPath ?? '/');
$primaryNav = $primaryNav ?? [];
$siteTitle = (string) ($site['title'] ?? 'Nutech Paper Products');
$logoPath = trim((string) ($site['logo_path'] ?? '/assets/img/nutech_square_logo.png'));
if ($logoPath === '') {
    $logoPath = '/assets/img/nutech_square_logo.png';
}
$logoUrl = preg_match('#^(https?:)?//#i', $logoPath) === 1
    ? $logoPath
    : ($logoPath[0] === '/' ? path_url($logoPath) : asset($logoPath));
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
        </div>
    </nav>
</header>
