<?php
declare(strict_types=1);
?>
<script src="<?= asset('assets/js/site.js') ?>"></script>
<?php if (!empty($footerScripts) && is_array($footerScripts)): ?>
    <?php foreach ($footerScripts as $script): ?>
        <?= (string) ($script['script_content'] ?? '') ?>
    <?php endforeach; ?>
<?php endif; ?>

