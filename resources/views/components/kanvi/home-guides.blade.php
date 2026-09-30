@php($guides = array_values(app(\App\Content\Articles::class)->guides()))
<section class="home-guides" id="guides" aria-labelledby="guides-heading">
    <div class="section-heading"><div><p class="section-kicker">Fra snak til en aftale</p><h2 id="guides-heading">Find en dag. Få alle med.</h2><p class="section-lead">Enkle råd til det, I gerne vil sammen.</p></div><a class="text-link" href="{{ route('articles.index') }}">Alle guides →</a></div>
    <div class="guide-card-grid">
        @foreach (array_slice($guides, 0, 3) as $article)
            <a class="guide-card" href="{{ url($article['path']) }}"><h3>{{ $article['label'] }}</h3><p>{{ $article['description'] }}</p><span class="guide-card-link">Læs guiden <span aria-hidden="true">→</span></span></a>
        @endforeach
    </div>
    <nav class="article-link-list" aria-label="Guides til jeres anledning">@foreach (array_slice($guides, 3) as $article)<a href="{{ url($article['path']) }}">{{ $article['label'] }} <span aria-hidden="true">↗</span></a>@endforeach</nav>
</section>
