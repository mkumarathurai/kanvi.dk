<x-layout :title="$poll->title" :private="true" :preview="$poll">
    <section class="flow screen-card poll-flow participant-flow" x-data="kanviPoll(@js($initial))" aria-label="Afstemning: {{ $poll->title }}">
        <section class="poll-intro" data-screen="intro" x-show="screen === 'intro'" aria-labelledby="intro-heading">
            <div class="intro-celebration" aria-hidden="true">🎉</div>
            <h1 id="intro-heading" tabindex="-1">{{ $poll->title }}</h1>
            <p class="lead small">Vi finder en dag, der passer flest muligt.</p>
            <div class="intro-facts"><x-kanvi.icon name="calendar" /><div><strong>{{ $poll->options->count() }} mulige datoer</strong><p>{{ $poll->options->map(fn ($option) => ucfirst($option->date_value->locale('da')->translatedFormat('D j. M')))->join(' · ') }}</p></div></div>
            <div class="intro-facts"><x-kanvi.icon name="people" /><div><strong>Find dagen sammen</strong><p>Svar Kan, Måske eller Kan ikke. Du kan rette, mens afstemningen er åben.</p></div></div>
            <div class="intro-art"><x-kanvi.calendar-sketch /></div>
            <x-kanvi.button @click="goTo('answers')">Giv dit svar <span aria-hidden="true">→</span></x-kanvi.button>
            <p class="hint centered">Ingen konto. Dine valg gemmes automatisk.</p>
            @if ($isAdmin)<button class="text-button" @click="goTo('results')">Se resultatet som arrangør →</button>@endif
        </section>

        <section class="answer-screen" data-screen="answers" x-cloak x-show="screen === 'answers'" aria-labelledby="answer-heading">
            <p class="wizard-context">{{ $poll->title }}</p>
            <h1 id="answer-heading" tabindex="-1">Hvad passer dig?</h1>
            <p class="lead small">Vælg Kan, Måske eller Kan ikke for hver dato.</p>
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
        </div>

            <div class="answer-actions">
                <x-kanvi.button @click="goTo('confirmation')" ::disabled="state !== 'saved' || !hasParticipant">Fortsæt <span aria-hidden="true">→</span></x-kanvi.button>
                <button type="button" class="text-button" x-show="canSeeResults" @click="goTo('results')">Se resultatet →</button>
            </div>
            <p class="hint centered" x-show="!hasParticipant">Skriv dit navn og vælg mindst ét svar for at fortsætte.</p>
        </section>

        <section class="confirmation-screen" data-screen="confirmation" x-cloak x-show="screen === 'confirmation'" aria-labelledby="confirmation-heading">
            <div class="saved-illustration" aria-hidden="true"><x-kanvi.icon name="check" :size="72" /></div>
            <h1 id="confirmation-heading" tabindex="-1">Tak for dit svar!</h1>
            <p class="lead small" x-text="state === 'saved' ? 'Dit svar er gemt. Du kan altid ændre det, mens afstemningen er åben.' : statusText"></p>
            <p class="response-progress"><x-kanvi.icon name="calendar" /><span>Du har svaret på <strong x-text="confirmedCount()"></strong> af {{ $poll->options->count() }} datoer.<span x-show="confirmedCount() < {{ $poll->options->count() }}"> Du kan svare på resten senere.</span></span></p>
            <x-kanvi.button @click="goTo('results')">Se resultatet <span aria-hidden="true">→</span></x-kanvi.button>
            <x-kanvi.button variant="secondary" @click="goTo('answers')">Rediger mit svar</x-kanvi.button>
        </section>

        <section class="poll-results" data-screen="results" x-cloak x-show="screen === 'results'" aria-labelledby="results-heading">
            <div class="poll-kicker"><span class="poll-state" :class="{ closed: !isOpen }" x-text="isOpen ? 'Afstemningen er åben' : 'Lukket for svar'"></span><button class="text-button" x-show="isOpen" @click="goTo('answers')" x-text="hasParticipant ? 'Rediger mit svar' : 'Giv dit svar'"></button></div>
            <h1 id="results-heading" class="poll-title" tabindex="-1">{{ $poll->title }}</h1>
            <div class="closed-notice" x-show="Object.keys(pending).length > 0"><p x-text="statusText"></p><button class="text-button" @click="goTo('answers')">Tilbage til mit svar</button></div>
            <div class="final-date-banner" x-show="finalDate"><p class="eyebrow">Så er dagen fundet ✓</p><h2 x-text="finalDate"></h2></div>
            <p class="closed-notice" x-show="!isOpen && !finalDate">Afstemningen er lukket for svar.</p>
            <div class="results-header">
                <h2>Hvilken dag passer bedst?</h2>
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
                        <details class="result-option" :class="{ 'best-option': option.best }"><summary>
                            <span class="result-icon" aria-hidden="true"><span x-show="option.best"><x-kanvi.icon name="trophy" :size="26" /></span><span x-show="!option.best"><x-kanvi.icon name="people" :size="26" /></span></span>
                            <div class="result-content">
                            <p class="best-label" x-show="option.best" x-text="results.options.filter(item => item.best).length > 1 ? 'Delt bedste mulighed' : 'Passer bedst lige nu'"></p>
                            <h3 x-text="option.label"></h3>
                            <x-kanvi.result-bar />
                            <p class="result-totals sr-only"><strong x-text="option.can + ' kan'"></strong><span x-text="option.maybe + ' måske'"></span><span x-text="option.cannot + ' kan ikke'"></span></p>
                            <p class="hint" x-show="option.unanswered > 0" x-text="option.unanswered + ' ubesvaret'"></p>
                            </div><span class="result-chevron" aria-hidden="true">›</span><span class="sr-only">Se individuelle svar</span></summary>
                            <div x-show="results.count > 0">
                                <ul class="individual-responses">
                                    <template x-for="person in option.people" :key="person.id">
                                        <li><span x-text="person.name"></span><span class="response-value" :class="person.value ?? 'unanswered'" x-text="labels[person.value ?? 'unanswered']"></span></li>
                                    </template>
                                </ul>
                            </div>
                        </details>
                    </template>
                    <p class="result-legend"><span><i class="can"></i>Kan</span><span><i class="maybe"></i>Måske</span><span><i class="cannot"></i>Kan ikke</span><span><i class="unanswered"></i>Ubesvaret</span></p>
                    <x-kanvi.participant-grid />
                </div>
            </template>
        </section>
        @if ($isAdmin)
        <div class="organizer-shortcuts" x-show="screen === 'results'">
            <a class="button secondary" href="{{ route('polls.manage', $poll) }}">Administrér afstemningen <span aria-hidden="true">→</span></a>
            <a class="text-link" href="{{ route('polls.share', $poll) }}">Del afstemningen <span aria-hidden="true">→</span></a>
        </div>
        @endif
        @unless ($isAdmin)
            <a class="text-link recovery-link" x-show="screen === 'intro' || screen === 'results'" href="{{ route('recovery.request', $poll) }}">Mistet arrangøradgangen?</a>
        @endunless
        <noscript><p class="error">Slå JavaScript til i browseren for at svare på afstemningen.</p></noscript>
    </section>
</x-layout>
