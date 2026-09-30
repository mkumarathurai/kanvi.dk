<x-layout :seo="$seo" :marketing="true">
    <article class="article-page">
        <nav class="article-breadcrumb" aria-label="Brødkrumme">
            <ol>@foreach ($breadcrumbs as $crumb)<li>@if ($loop->last)<span aria-current="page">{{ $crumb['label'] }}</span>@else<a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a><span aria-hidden="true">→</span>@endif</li>@endforeach</ol>
        </nav>
        <header class="article-header">
            <p class="article-kicker">{{ $article['group'] }}</p>
            <h1>{{ $article['title'] }}</h1>
            <p class="article-intro">{{ $article['description'] }}</p>
            @if ($article['kind'] === 'guide')<p class="article-byline">Af Kanvi · Find ud af det sammen.</p>@endif
        </header>
        <div class="article-layout">
            <div class="article-prose">{!! $article['html'] !!}</div>
            <aside class="article-sidebar" aria-label="Navigation i artiklen">
                <details class="article-contents">
                    <summary>I denne artikel <span aria-hidden="true">↓</span></summary>
                    <ol>@foreach ($article['contents'] as $section)<li><a href="#{{ $section['id'] }}">{{ $section['title'] }}</a></li>@endforeach</ol>
                </details>
                <div class="article-side-cta"><x-kanvi.calendar-sketch /><h2>Skal vi finde en dag?</h2><p>Foreslå datoer. Del linket. Find den dag, der passer bedst.</p><a href="{{ route('polls.create') }}" class="button primary">Opret afstemning →</a><span>Gratis · Ingen konto</span></div>
            </aside>
        </div>
        @if ($article['kind'] === 'guide')
        <section class="article-related" aria-labelledby="related-heading">
            <p class="article-kicker">Find mere inspiration</p><h2 id="related-heading">Hvad skal I finde en dag til?</h2>
            <div class="article-link-list">@foreach ($articles as $key => $related)@if ($key !== $article['key'])<a href="{{ url($related['path']) }}">{{ $related['label'] }} <span aria-hidden="true">↗</span></a>@endif @endforeach</div>
        </section>
        @endif
    </article>
</x-layout>
