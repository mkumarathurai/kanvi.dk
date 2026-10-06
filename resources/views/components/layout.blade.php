@props(['title' => 'Find en dag, der passer alle', 'private' => false, 'preview' => null, 'home' => false, 'seo' => null, 'marketing' => false])
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/kanvi-mark.svg') }}?v=2">
    <meta name="theme-color" content="#FFFDF8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $seo['description'] ?? 'Find en dag, der passer gruppen. Opret en datoafstemning uden konto.' }}">
    @if ($seo)
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Kanvi">
        <meta property="og:locale" content="da_DK">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        @isset ($seo['image'])
            <meta property="og:image" content="{{ $seo['image'] }}">
            <meta property="og:image:type" content="image/png">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="{{ $seo['title'] }}">
        @endisset
        <meta name="twitter:card" content="{{ isset($seo['image']) ? 'summary_large_image' : 'summary' }}">
        @php
            $graph = [['@type' => 'WebPage', 'name' => $seo['title'], 'description' => $seo['description'], 'url' => $seo['canonical'], 'inLanguage' => 'da', 'publisher' => ['@type' => 'Organization', 'name' => 'Kanvi']]];
            if (isset($seo['breadcrumbs'])) {
                $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn ($crumb, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['label'], 'item' => $crumb['url']], $seo['breadcrumbs'], array_keys($seo['breadcrumbs']))];
            }
            if (isset($seo['article'])) {
                $graph[] = ['@type' => 'Article', 'headline' => $seo['article']['headline'], 'datePublished' => $seo['article']['datePublished'], 'dateModified' => $seo['article']['dateModified'], 'author' => ['@type' => 'Organization', 'name' => 'Kanvi'], 'publisher' => ['@type' => 'Organization', 'name' => 'Kanvi'], 'image' => $seo['image'], 'mainEntityOfPage' => $seo['canonical'], 'inLanguage' => 'da'];
            }
        @endphp
        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif
    @if ($private)
        <meta name="robots" content="noindex, nofollow">
        <meta name="referrer" content="no-referrer">
    @endif
    @if ($preview)
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Kanvi">
        <meta property="og:locale" content="da_DK">
        <meta property="og:title" content="{{ $preview->title }}">
        <meta property="og:description" content="Find en dag, der passer gruppen. Svar på Kanvi →">
        <meta property="og:url" content="{{ app(\App\Domain\Polls\Services\PollLinks::class)->route('polls.show', $preview) }}">
        <meta property="og:image" content="{{ app(\App\Domain\Polls\Services\PollLinks::class)->route('polls.preview', $preview) }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $preview->title }} — Find en dag, der passer gruppen. Svar på Kanvi.">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <title>{{ $seo['title'] ?? $title.' · Kanvi' }}</title>
    @if (app()->environment('production') && ! $private)
        <script async src="https://stats.mathi.dev/script.js" data-website-id="fa2c9fe6-7537-4afb-835c-f47f75a9d546" data-domains="kanvi.dk,www.kanvi.dk" data-exclude-search="true" data-exclude-hash="true" referrerpolicy="no-referrer"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <a class="logo-link" href="{{ route('home') }}" aria-label="Kanvi — forsiden"><x-kanvi.logo /></a>
            @if ($home || $marketing)
                <nav class="home-nav" aria-label="Hovedmenu"><a href="{{ route('home') }}#saadan-virker-det">Sådan virker det</a><a href="{{ route('home') }}#proev-selv">Prøv et eksempel</a><a href="{{ route('articles.index') }}">Guides</a></nav>
                <a class="header-create" href="{{ route('polls.create') }}">Opret afstemning <span aria-hidden="true">↗</span></a>
            @else
                <span class="brand-note">Find ud af det sammen.</span>
            @endif
        </header>
        <main id="main">{{ $slot }}</main>
        <footer class="site-footer">
            @unless ($private)<nav class="footer-content-links" aria-label="Mere om Kanvi"><a href="{{ route('articles.index') }}">Guides og inspiration</a><a href="{{ route('articles.situations') }}">Find en dag til …</a><a href="{{ route('articles.faq') }}">Spørgsmål og svar</a><a href="{{ route('articles.help') }}">Hjælp</a></nav>@endunless
            {{-- Participants hand over their name on private pages, so this link belongs there too. --}}
            <nav class="footer-legal-links" aria-label="Privatliv og data"><a href="{{ route('articles.privatliv') }}">Privatliv og data</a></nav>
            <div lang="en">
            <p>Made with <span role="img" aria-label="love">❤️</span></p>
            <p>© {{ now()->year }} Mathi Kumarathurai. All rights reserved.</p>
            </div>
        </footer>
    </div>
    @livewireScripts
</body>
</html>
