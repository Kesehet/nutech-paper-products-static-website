<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<?php require BASE_PATH . '/templates/partials/head.php'; ?>
<body class="bg-background-light text-slate-900 font-display antialiased">
<?php require BASE_PATH . '/templates/partials/header.php'; ?>
<main>
<?= $content ?>
</main>
<?php require BASE_PATH . '/templates/partials/footer.php'; ?>
<?php require BASE_PATH . '/templates/partials/scripts.php'; ?>
</body>
</html>

