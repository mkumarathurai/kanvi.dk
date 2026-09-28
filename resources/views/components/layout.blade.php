@props(['title' => 'Find en dag, der passer alle', 'private' => false, 'preview' => null, 'home' => false])
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/kanvi-mark.svg') }}">
    <meta name="theme-color" content="#FFFDF8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find en dag, der passer gruppen. Opret en datoafstemning uden konto.">
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
    <title>{{ $title }} · Kanvi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <a class="logo-link" href="{{ route('home') }}" aria-label="Kanvi — forsiden"><x-kanvi.logo /></a>
            @if ($home)
                <nav class="home-nav" aria-label="Hovedmenu"><a href="#saadan-virker-det">Sådan virker det</a><a href="#eksempel">Se et eksempel</a></nav>
                <a class="header-create" href="#title">Opret afstemning <span aria-hidden="true">↗</span></a>
            @else
                <span class="brand-note">Find ud af det sammen.</span>
            @endif
        </header>
        <main id="main">{{ $slot }}</main>
        <footer class="site-footer">Lidt mindre planlægning. Lidt mere sammen.</footer>
    </div>
    @livewireScripts
</body>
</html>
