<?php
declare(strict_types=1);
?>
<section class="py-28">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <p class="text-primary font-bold uppercase tracking-widest mb-4">404</p>
        <h1 class="text-5xl font-black text-dark-navy mb-4">Page Not Found</h1>
        <p class="text-slate-600 mb-8">The page you requested does not exist or has been moved.</p>
        <a class="inline-flex items-center px-6 py-3 bg-primary text-dark-navy rounded-lg font-bold hover:bg-primary-hover transition-colors" href="<?= e(path_url('/')) ?>">
            Back to Homepage
        </a>
    </div>
</section>
