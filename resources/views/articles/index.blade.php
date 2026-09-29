<x-layout :seo="$seo" :marketing="true">
    <div class="article-page guide-index">
        <nav class="article-breadcrumb" aria-label="Brødkrumme"><ol><li><a href="{{ route('home') }}">Forside</a><span aria-hidden="true">→</span></li><li aria-current="page">{{ request()->routeIs('articles.situations') ? 'Til' : 'Guides' }}</li></ol></nav>
        <header class="article-header"><p class="article-kicker">Guides og inspiration</p><h1>{{ $title }}</h1><p class="article-intro">En middag, en arbejdsdag eller den store familiefest. Her finder du hjælp til at få datoen på plads.</p></header>
        <div class="guide-card-grid">@foreach ($articles as $article)<a class="guide-card" href="{{ url($article['path']) }}"><span class="article-kicker">{{ $article['group'] }}</span><h2>{{ $article['title'] }}</h2><p>{{ $article['description'] }}</p><span class="guide-card-link">Læs guiden <span aria-hidden="true">→</span></span></a>@endforeach</div>
        <div class="guide-index-cta"><h2>Har du allerede nogle datoer i tankerne?</h2><a href="{{ route('polls.create') }}" class="button primary">Opret en datoafstemning →</a></div>
    </div>
</x-layout>
