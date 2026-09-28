<x-layout :title="$poll->title" :private="true" :preview="$poll">
    <section class="flow screen-card poll-flow" x-data="kanviPoll(@js($initial))" aria-labelledby="poll-heading">
        <div class="poll-kicker"><p class="eyebrow">Find en dag sammen</p><span class="poll-state" :class="{ closed: !isOpen }" x-text="isOpen ? 'Afstemningen er åben' : 'Lukket for svar'"></span></div>
        <h1 class="compact-heading poll-title" id="poll-heading">{{ $poll->title }}</h1>
        <p class="lead small" x-show="isOpen">Vælg, hvad der passer dig. Du kan altid rette dine svar, mens afstemningen er åben.</p>

        <div class="final-date-banner" x-cloak x-show="finalDate">
            <p class="eyebrow">Så er dagen fundet ✓</p>
            <h2 x-text="finalDate"></h2>
            <p>Arrangøren har valgt datoen. Afstemningen er lukket for svar.</p>
        </div>
        <div class="closed-notice" x-cloak x-show="!isOpen && !finalDate">Afstemningen er lukket for svar.</div>
        <div class="options-changed" x-cloak x-show="optionsChanged">Datoerne er ændret. <a href="{{ route('polls.show', $poll) }}">Genindlæs for at se de aktuelle muligheder.</a></div>
        <div x-show="isOpen || hasParticipant">
        <div class="participant-name">
            <label for="participant-name">Dit navn</label>
            <x-kanvi.input id="participant-name" type="text" maxlength="80" autocomplete="given-name" placeholder="Hvad hedder du?"
                ::value="name" @input="changeName($event.target.value)" ::disabled="!isOpen" aria-describedby="name-help save-status" />
            <p class="hint" id="name-help">Dit navn og dine svar kan ses af arrangøren og dem, der har svaret.</p>
        </div>

        <div class="vote-dates">
            @foreach ($poll->options as $option)
                <fieldset class="vote-date" :disabled="!isOpen">
                    <legend><x-kanvi.icon name="calendar" :size="18" />{{ ucfirst($option->date_value->locale('da')->translatedFormat('l')) }}, {{ $option->date_value->translatedFormat('j. F Y') }}</legend>
                    <div class="vote-choices" role="group" aria-label="Svar for {{ $option->date_value->translatedFormat('j. F Y') }}">
                        @foreach (['can' => 'Kan', 'maybe' => 'Måske', 'cannot' => 'Kan ikke'] as $value => $label)
                            <x-kanvi.availability-choice :option="$option->id" :value="$value" :label="$label" />
                        @endforeach
                    </div>
                    <p class="hint unanswered" x-show="!answers['{{ $option->id }}']">Ikke besvaret endnu</p>
                </fieldset>
            @endforeach
        </div>
        <x-kanvi.autosave-status />
        <noscript><p class="error">Slå JavaScript til i browseren for at svare på afstemningen.</p></noscript>
        </div>

        <section class="poll-results" aria-labelledby="results-heading">
            <div class="results-header">
                <h2 id="results-heading">Hvilken dag passer bedst?</h2>
                <button type="button" class="text-button" x-cloak x-show="canSeeResults" @click="refreshResults()" :disabled="loadingResults">Opdatér</button>
            </div>
            <p class="hint" x-show="!canSeeResults && isOpen">Du kan se resultatet, når dit navn og første svar er gemt.</p>
            <p class="hint" x-cloak x-show="!canSeeResults && !isOpen">Deltagernes svar vises kun til arrangøren og dem, der nåede at svare.</p>
            <p class="hint" role="status" x-cloak x-show="loadingResults && !results">Henter resultat…</p>
            <p class="error" role="status" x-cloak x-show="resultError" x-text="resultError"></p>
            <template x-if="results && canSeeResults">
                <div>
                    <p class="result-count"><span x-text="results.count"></span> har svaret<span x-show="results.incomplete > 0"> · <span x-text="results.incomplete"></span> mangler at svare på alle datoer</span></p>
                    <p class="hint" x-show="results.count === 0">Ingen har svaret endnu.<span x-show="isOpen"> Del linket med gruppen for at finde en dag.</span></p>
                    <template x-for="option in results.options" :key="option.id">
                        <article class="result-option" :class="{ 'best-option': option.best }">
                            <span class="result-icon" aria-hidden="true"><span x-show="option.best"><x-kanvi.icon name="trophy" :size="26" /></span><span x-show="!option.best"><x-kanvi.icon name="people" :size="26" /></span></span>
                            <div class="result-content">
                            <p class="best-label" x-show="option.best" x-text="results.options.filter(item => item.best).length > 1 ? 'Delt bedste mulighed' : 'Passer bedst lige nu'"></p>
                            <h3 x-text="option.label"></h3>
                            <x-kanvi.result-bar />
                            <p class="result-totals"><strong x-text="option.can + ' kan'"></strong><span x-text="option.maybe + ' måske'"></span><span x-text="option.cannot + ' kan ikke'"></span></p>
                            <p class="hint" x-show="option.unanswered > 0" x-text="option.unanswered + ' har ikke svaret på denne dato'"></p>
                            <details x-show="results.count > 0">
                                <summary>Se hvem der kan</summary>
                                <ul class="individual-responses">
                                    <template x-for="person in option.people" :key="person.id">
                                        <li><span x-text="person.name"></span><span class="response-value" :class="person.value ?? 'unanswered'" x-text="labels[person.value ?? 'unanswered']"></span></li>
                                    </template>
                                </ul>
                            </details>
                            </div>
                        </article>
                    </template>
                    <x-kanvi.participant-grid />
                </div>
            </template>
        </section>
        @if ($isAdmin)
            <a class="button primary" href="{{ route('polls.manage', $poll) }}">Administrér afstemningen <span aria-hidden="true">→</span></a>
            <a class="button secondary" href="{{ route('polls.share', $poll) }}">Del afstemningen <span aria-hidden="true">→</span></a>
        @endif
        @unless ($isAdmin)
            <a class="text-link recovery-link" href="{{ route('recovery.request', $poll) }}">Mistet arrangøradgangen?</a>
        @endunless
    </section>
</x-layout>
