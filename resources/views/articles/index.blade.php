<x-layout :seo="$seo" :marketing="true">
    <div class="article-page guide-index">
        <nav class="article-breadcrumb" aria-label="Brødkrumme"><ol><li><a href="{{ route('home') }}">Forside</a><span aria-hidden="true">→</span></li><li aria-current="page">{{ $index['crumb'] }}</li></ol></nav>
        <header class="article-header"><p class="article-kicker">{{ $index['kicker'] }}</p><h1>{{ $index['heading'] }}</h1><p class="article-intro">{{ $index['intro'] }}</p></header>
        <div class="guide-card-grid">@foreach ($articles as $article)<a class="guide-card" href="{{ url($article['path']) }}"><span class="article-kicker">{{ $article['group'] }}</span><h2>{{ $article['title'] }}</h2><p>{{ $article['description'] }}</p><span class="guide-card-link">{{ $index['kind'] === 'help' ? 'Læs vejledningen' : 'Læs guiden' }} <span aria-hidden="true">→</span></span></a>@endforeach
            @if ($index['kind'] === 'help')
                {{-- One privacy page, linked from here rather than duplicated under /hjaelp. --}}
                <a class="guide-card" href="{{ url('/privatliv') }}"><span class="article-kicker">Om Kanvi</span><h2>Privatliv og data</h2><p>Hvad Kanvi gemmer, hvem der kan se det, hvor længe det bliver gemt, og hvordan du får dine data slettet.</p><span class="guide-card-link">Læs den <span aria-hidden="true">→</span></span></a>
            @endif
        </div>
        <div class="guide-index-cta"><h2>Har du allerede nogle datoer i tankerne?</h2><a href="{{ route('polls.create') }}" class="button primary">Opret en datoafstemning →</a></div>
    </div>
</x-layout>
