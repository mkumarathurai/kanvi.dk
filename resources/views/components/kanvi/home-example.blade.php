@php
    $days = [['Fre', '9. okt.'], ['Lør', '10. okt.'], ['Fre', '16. okt.'], ['Lør', '17. okt.'], ['Søn', '18. okt.']];
    $people = [
        'Mette' => ['can', 'can', 'maybe', 'can', 'cannot'],
        'Jens' => ['can', 'maybe', 'cannot', 'can', 'maybe'],
        'Louise' => ['maybe', 'can', 'can', 'maybe', 'cannot'],
        'Peter' => ['can', 'can', 'cannot', 'can', 'can'],
        'Katrine' => ['cannot', 'can', 'can', 'maybe', 'can'],
    ];
@endphp
<aside class="home-example" id="eksempel" aria-label="Eksempel på en afstemning">
    <div class="example-orbit" aria-hidden="true"></div>
    <div class="floating-answer answer-mette" aria-hidden="true"><span class="example-avatar avatar-mette">M</span><p>Mette<span>Kan <strong>fredag</strong></span></p></div>
    <div class="floating-answer answer-jens" aria-hidden="true"><span class="example-avatar avatar-jens">J</span><p>Jens<span>Måske lørdag</span></p></div>
    <div class="floating-answer answer-louise" aria-hidden="true"><span class="example-avatar avatar-louise">L</span><p>Louise<span>Kan ikke søndag</span></p></div>
    <x-kanvi.card class="example-poll">
        <div class="example-top"><x-kanvi.logo /><span class="example-tag">Et eksempel</span></div>
        <h2>Sommerfest med naboerne <span aria-hidden="true">🎉</span></h2>
        <p class="hint">5 datoer · 5 fiktive deltagere</p>
        <table class="example-table">
            <caption class="sr-only">Eksempel: lørdag den 10. oktober passer flest</caption>
            <thead><tr><td></td>@foreach ($days as [$day, $date])<th scope="col" @class(['example-best' => $loop->index === 1])>{{ $day }}<span>{{ $date }}</span></th>@endforeach</tr></thead>
            <tbody>
                @foreach ($people as $name => $answers)
                    <tr><th scope="row"><span class="example-avatar avatar-{{ strtolower($name) }}" aria-hidden="true">{{ mb_substr($name, 0, 1) }}</span>{{ $name }}</th>
                    @foreach ($answers as $answer)
                        <td @class(['example-best' => $loop->index === 1])><span class="mini-answer {{ $answer }}"><span aria-hidden="true">{{ ['can' => '✓', 'maybe' => '−', 'cannot' => '×'][$answer] }}</span><span class="sr-only">{{ ['can' => 'Kan', 'maybe' => 'Måske', 'cannot' => 'Kan ikke'][$answer] }}</span></span></td>
                    @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><th scope="row"><span class="sr-only">Antal der kan</span></th>@for ($day = 0; $day < 5; $day++)<td @class(['example-best' => $day === 1])>{{ collect($people)->filter(fn ($answers) => $answers[$day] === 'can')->count() }}<span>kan</span></td>@endfor</tr></tfoot>
        </table>
    </x-kanvi.card>
    <div class="hand-note example-note" aria-hidden="true">Se straks,<br>hvilken dag<br>der passer bedst<svg viewBox="0 0 100 65" fill="none"><path d="M5 5c0 60 64 57 85 8m-17 8 18-10 3 21" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
    <div class="hand-note example-note-top" aria-hidden="true">Hurtigt<br>og nemt<br>for alle<svg viewBox="0 0 65 85" fill="none"><path d="M52 5c8 38-12 49-40 58m6-17L9 65l22 3" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
    <x-kanvi.calendar-sketch class="example-calendar" />
</aside>
