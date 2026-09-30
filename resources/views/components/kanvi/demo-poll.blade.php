<div class="demo-poll" x-data="kanviDemo()">
    <h3>Sommerfest med naboerne <span aria-hidden="true">🎉</span></h3>
    <p class="demo-explanation">Prøveafstemning · dine valg gemmes ikke.</p>
    <h4 class="demo-your-answers">Dine svar</h4>
    @foreach (['Fre. 9. oktober', 'Lør. 10. oktober', 'Fre. 16. oktober', 'Lør. 17. oktober', 'Søn. 18. oktober'] as $date)
        <fieldset class="demo-date" @if ($loop->index > 1) x-show="expanded" x-cloak @endif>
            <legend>{{ $date }}</legend>
            <div class="demo-choices">
                @foreach (['can' => ['✓', 'Kan'], 'maybe' => ['−', 'Måske'], 'cannot' => ['×', 'Kan ikke']] as $value => [$symbol, $label])
                    <button type="button" class="demo-choice {{ $value }}" disabled x-bind:disabled="false" x-on:click="choose({{ $loop->parent->index }}, '{{ $value }}')" x-bind:class="{ 'is-selected': answers[{{ $loop->parent->index }}] === '{{ $value }}' }" x-bind:aria-pressed="answers[{{ $loop->parent->index }}] === '{{ $value }}'" aria-pressed="false"><span aria-hidden="true">{{ $symbol }}</span>{{ $label }}</button>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    <div class="demo-actions"><button type="button" class="button primary" disabled x-bind:disabled="false" x-on:click="expanded = !expanded" x-bind:aria-expanded="expanded" aria-expanded="false" aria-controls="demo-results"><span x-text="expanded ? 'Vis færre datoer' : 'Se hele eksemplet'">Se hele eksemplet</span><span aria-hidden="true">→</span></button><button type="button" class="text-button" x-show="hasAnswers" x-cloak x-on:click="reset()">Start forfra</button></div>
    <p class="demo-feedback" role="status" aria-live="polite" x-text="hasAnswers ? 'Dine valg er med i resultatet som Dig.' : 'Vælg ét svar pr. dato og se resultatet nedenfor.'"></p>
    <div class="demo-results" id="demo-results" x-show="expanded" x-cloak>
        <h4>Hvilken dag passer bedst?</h4>
        <p x-text="hasAnswers ? '5 fiktive deltagere + dig · opdateres, når du vælger.' : '5 fiktive deltagere har svaret. Prøv at tilføje dine svar.'"></p>
        <ol>
            <template x-for="option in results.options" :key="option.id">
                <li :class="{ 'demo-best': isBest(option.id) }">
                    <div class="demo-result-heading"><strong x-text="option.label"></strong><span class="demo-best-label" x-show="isBest(option.id)">Passer flest</span></div>
                    <x-kanvi.result-bar />
                    <p class="demo-result-counts"><span x-text="option.can + ' kan · ' + option.maybe + ' måske · ' + option.cannot + ' kan ikke'"></span><span x-show="option.unanswered > 0" x-text="' · ' + option.unanswered + ' ubesvaret'"></span></p>
                </li>
            </template>
        </ol>
        <details class="demo-people"><summary>Se deltagernes svar</summary><x-kanvi.participant-grid /></details>
        <a class="text-link" href="{{ route('polls.create') }}">Lav din egen afstemning <span aria-hidden="true">→</span></a>
    </div>
    <noscript><p class="hint">Slå JavaScript til for at prøve svarene, eller <a href="{{ route('polls.create') }}">opret din egen afstemning</a>.</p></noscript>
</div>
