<div @class(['create-flow', 'home-flow' => $step === 1, 'flow' => $step !== 1])>
    @if ($step === 1)
        <div class="home-hero">
        <form wire:submit="next" class="intro" aria-labelledby="create-heading">
            <p class="eyebrow">Planer er bedre sammen</p>
            <h1 id="create-heading">Find en dag,<br>der passer <span class="hand-underline">alle.</span><svg class="headline-rays" viewBox="0 0 52 44" fill="none" aria-hidden="true"><path d="m8 24 3-18m16 28 15-15" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg></h1>
            <p class="lead">Opret en afstemning, og find den bedste dag sammen. Ingen konto nødvendig.</p>
            <div class="field-group">
                <label for="title">Hvad skal vi finde en dag til?</label>
                <div class="input-with-icon"><x-kanvi.icon name="calendar" /><x-kanvi.input id="title" wire:model="title" maxlength="140" autocomplete="off" placeholder="Fx sommerfest med naboerne" aria-describedby="title-error" :aria-invalid="$errors->has('title') ? 'true' : 'false'" /></div>
                <p id="title-error" class="error" role="alert">@error('title') {{ $message }} @enderror</p>
            </div>
            <x-kanvi.button type="submit" wire:loading.attr="disabled" wire:target="next">Find nogle datoer <span aria-hidden="true">→</span></x-kanvi.button>
            <ul class="home-benefits"><li><x-kanvi.icon name="check" :size="18" /> Gratis</li><li><x-kanvi.icon name="people" :size="18" /> Ingen konto</li><li><x-kanvi.icon name="bolt" :size="18" /> Klar på 30 sekunder</li></ul>
            <div class="mobile-hero-note"><x-kanvi.calendar-sketch /><p class="hand-note">Til familie,<br>venner, foreninger<br>og meget mere.</p></div>
        </form>
        <x-kanvi.home-example />
        </div>
        <section class="how-it-works" id="saadan-virker-det" aria-labelledby="how-heading">
            <h2 id="how-heading" class="sr-only">Sådan virker Kanvi</h2>
            <ol>
                @foreach ([['calendar', 'Opret afstemning', 'Skriv en titel og vælg datoer'], ['link', 'Del afstemning', 'Få et link og del med din gruppe'], ['check', 'Deltagerne svarer', 'Kan, Måske eller Kan ikke'], ['trophy', 'Se resultatet', 'Find den dag, der passer bedst'], ['edit', 'Vælg dagen', 'Vælg dato, luk eller genåbn']] as [$icon, $heading, $description])
                    <li><span>{{ $loop->iteration }}</span><div><h3>{{ $heading }}</h3><p>{{ $description }}</p></div><x-kanvi.icon :name="$icon" :size="28" /></li>
                @endforeach
            </ol>
        </section>
    @elseif ($step === 2)
        <section class="screen-card creation-card" aria-labelledby="dates-heading">
            <button type="button" class="back-link" wire:click="back">← <span>{{ $title }}</span></button>
            <div class="creation-progress" aria-label="Trin 2 af 3"><span>✓</span><i></i><span class="active">2</span><i></i><span>3</span></div>
            <h1 class="compact-heading" id="dates-heading">Hvornår kunne det være?</h1>
            <p class="lead small">Vælg mindst to datoer. Tidspunktet kan I aftale senere.</p>
            <div class="calendar-layout">
            <div class="calendar" aria-label="Vælg mulige datoer">
                <div class="calendar-header">
                    <button type="button" class="icon-button" wire:click="changeMonth(-1)" aria-label="Forrige måned">←</button>
                    <h2 aria-live="polite">{{ ucfirst($calendarMonth->translatedFormat('F Y')) }}</h2>
                    <button type="button" class="icon-button" wire:click="changeMonth(1)" aria-label="Næste måned">→</button>
                </div>
                <div class="calendar-grid">
                    @foreach (['ma', 'ti', 'on', 'to', 'fr', 'lø', 'sø'] as $weekday)
                        <span class="weekday" aria-hidden="true">{{ $weekday }}</span>
                    @endforeach
                    @for ($blank = 1; $blank < $calendarMonth->dayOfWeekIso; $blank++)
                        <span aria-hidden="true"></span>
                    @endfor
                    @foreach ($days as $day)
                        <button type="button" wire:key="date-{{ $day->toDateString() }}" wire:click="toggleDate('{{ $day->toDateString() }}')"
                            @class(['day', 'selected' => in_array($day->toDateString(), $dates, true), 'today' => $day->isToday()])
                            aria-label="{{ $day->translatedFormat('l j. F Y') }}"
                            aria-pressed="{{ in_array($day->toDateString(), $dates, true) ? 'true' : 'false' }}">
                            {{ $day->day }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="selected-dates">
                <h2>Valgte datoer <span class="count">{{ count($dates) }}</span></h2>
                @if (count($dates) === 0)
                    <p class="hint">Tryk på dagene i kalenderen.</p>
                @else
                    <ul>
                        @foreach ($dates as $date)
                            <li wire:key="selected-{{ $date }}">
                                <span>{{ ucfirst(\Carbon\CarbonImmutable::parse($date)->locale('da')->translatedFormat('D j. F Y')) }}</span>
                                <button type="button" class="icon-button remove-date" wire:click="toggleDate('{{ $date }}')" aria-label="Fjern {{ $date }}">×</button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            </div>
            @if ($errors->any())
                <div class="error" role="alert">{{ $errors->first() }}</div>
            @endif
            <x-kanvi.button class="calendar-continue" wire:click="review" wire:loading.attr="disabled" :disabled="count($dates) < 2">Fortsæt <span aria-hidden="true">→</span></x-kanvi.button>
            <p class="hint centered">Tjek dine valg, før du opretter.</p>
        </section>
    @else
        <section class="screen-card creation-card review-card" aria-labelledby="review-heading">
            <button type="button" class="back-link" wire:click="back">← Tilbage til datoerne</button>
            <div class="creation-progress" aria-label="Trin 3 af 3"><span>✓</span><i></i><span>✓</span><i></i><span class="active">3</span></div>
            <h1 class="compact-heading" id="review-heading">Er alt klar?</h1>
            <p class="lead small">Her er overblikket over din afstemning.</p>
            <div class="creation-summary">
                <h2>{{ $title }}</h2>
                <div><x-kanvi.icon name="calendar" /><p><strong>{{ count($dates) }} datoer</strong><span>{{ collect($dates)->map(fn ($date) => ucfirst(\Carbon\CarbonImmutable::parse($date)->locale('da')->translatedFormat('D j. F')))->join(' · ') }}</span></p></div>
                <div><x-kanvi.icon name="people" /><p>Alle med linket kan svare uden konto.</p></div>
                <div><x-kanvi.icon name="lock" /><p>Resultater vises efter første gemte svar.</p></div>
            </div>
            <p class="review-note"><x-kanvi.icon name="check" />Du kan tilføje og fjerne datoer, mens afstemningen er åben.</p>
            @if ($errors->any()) <p class="error" role="alert">{{ $errors->first() }}</p> @endif
            <button type="button" class="button primary" wire:click="create" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="create">Opret afstemning</span>
                <span wire:loading wire:target="create">Opretter…</span><span aria-hidden="true">→</span>
            </button>
        </section>
    @endif
</div>
