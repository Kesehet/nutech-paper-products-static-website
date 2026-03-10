<?php
declare(strict_types=1);

$hero = $page['sections']['about.hero']['content'] ?? [];
?>
<section class="relative py-24 bg-dark-navy text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-primary text-xs font-bold uppercase tracking-widest mb-4">Precision and Quality</p>
        <h1 class="text-4xl md:text-6xl font-black leading-tight mb-6">
            <?= e((string) ($hero['heading'] ?? 'Pioneering Paper Excellence Since 1995')) ?>
        </h1>
        <p class="max-w-3xl text-slate-300 text-lg">
            <?= e((string) ($hero['description'] ?? 'Nutech Paper Products is a leader in self-adhesive paper manufacturing, delivering innovative B2B solutions across global industries.')) ?>
        </p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4">Our Story</h2>
            <p class="text-slate-600 leading-relaxed">
                Nutech Paper Products began with a vision to provide high-quality specialty paper solutions to industrial clients.
                Over the years, we have expanded into a trusted B2B manufacturer known for consistency, technical expertise, and long-term delivery partnerships.
            </p>
        </div>
        <div>
            <img alt="Nutech production line" class="rounded-xl shadow-xl w-full h-[420px] object-cover" src="https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=1200&q=80">
        </div>
    </div>
</section>

<section class="py-20 bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-10">Our Expertise</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <article class="bg-white p-8 rounded-xl border border-slate-200">
                <h3 class="text-xl font-bold mb-3">Self-Adhesive Solutions</h3>
                <p class="text-slate-600">High-performance adhesive-backed paper stocks for demanding applications.</p>
            </article>
            <article class="bg-white p-8 rounded-xl border border-slate-200">
                <h3 class="text-xl font-bold mb-3">Specialized Films</h3>
                <p class="text-slate-600">Engineered substrates designed for smooth converting and print compatibility.</p>
            </article>
            <article class="bg-white p-8 rounded-xl border border-slate-200">
                <h3 class="text-xl font-bold mb-3">Custom Coating</h3>
                <p class="text-slate-600">Tailored coating systems to match release and adhesion performance targets.</p>
            </article>
        </div>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4">Industries Served</h2>
        <p class="text-slate-600 max-w-3xl">
            Our products support packaging, pharmaceuticals, logistics, consumer labeling, and manufacturing supply chains where consistency and uptime are critical.
        </p>
    </div>
</section>

<section class="py-20 bg-primary/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-dark-navy text-white rounded-2xl p-10 md:p-14">
            <h2 class="text-3xl md:text-4xl font-black mb-6">Quality Commitment</h2>
            <ul class="space-y-3 text-slate-300">
                <li>ISO-aligned production processes</li>
                <li>Batch-wise quality checks and traceability</li>
                <li>Reliable lead times for recurring B2B demand</li>
            </ul>
        </div>
    </div>
</section>

