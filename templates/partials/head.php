<?php
declare(strict_types=1);

$meta = $meta ?? [];
$siteTitle = (string) ($site['title'] ?? env('APP_NAME', 'Nutech Paper Products'));
$primaryColor = (string) ($theme['primary_color'] ?? '#67C6D0');
$primaryHover = (string) ($theme['primary_hover_color'] ?? '#2F8FA1');
$darkNavy = (string) ($theme['dark_navy_color'] ?? '#0F1B2A');
$backgroundLight = (string) ($theme['background_light_color'] ?? '#F8FAFC');
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($headStartScripts) && is_array($headStartScripts)): ?>
        <?php foreach ($headStartScripts as $script): ?>
            <?= (string) ($script['script_content'] ?? '') ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <title><?= e((string) ($meta['title'] ?? $siteTitle)) ?></title>
    <meta name="description" content="<?= e((string) ($meta['description'] ?? '')) ?>">
    <?php if (!empty($meta['keywords'])): ?>
    <meta name="keywords" content="<?= e((string) $meta['keywords']) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e((string) ($meta['robots'] ?? 'index,follow')) ?>">
    <?php if (!empty($meta['canonical'])): ?>
    <link rel="canonical" href="<?= e((string) $meta['canonical']) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= e((string) ($meta['og_title'] ?? ($meta['title'] ?? $siteTitle))) ?>">
    <meta property="og:description" content="<?= e((string) ($meta['og_description'] ?? ($meta['description'] ?? ''))) ?>">
    <?php if (!empty($meta['og_image'])): ?>
    <meta property="og:image" content="<?= e((string) $meta['og_image']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['schema_json'])): ?>
    <script type="application/ld+json"><?= (string) $meta['schema_json'] ?></script>
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: ["class"],
            theme: {
                extend: {
                    colors: {
                        primary: "<?= e($primaryColor) ?>",
                        "primary-hover": "<?= e($primaryHover) ?>",
                        "dark-navy": "<?= e($darkNavy) ?>",
                        "background-light": "<?= e($backgroundLight) ?>",
                        "background-dark": "<?= e($darkNavy) ?>"
                    },
                    fontFamily: {
                        display: ["Plus Jakarta Sans", "sans-serif"]
                    }
                }
            }
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
    <?php if (!empty($headEndScripts) && is_array($headEndScripts)): ?>
        <?php foreach ($headEndScripts as $script): ?>
            <?= (string) ($script['script_content'] ?? '') ?>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
