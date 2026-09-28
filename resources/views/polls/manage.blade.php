<x-layout :title="'Administrér · '.$poll->title" :private="true">
    <section class="flow screen-card manage-flow" aria-labelledby="manage-heading">
        <nav class="management-nav" aria-label="Afstemningen">
            <a href="{{ route('polls.show', $poll) }}">← Se afstemningen</a>
            <a href="{{ route('polls.share', $poll) }}">Del linket ↗</a>
        </nav>
        <p class="eyebrow">{{ ['open' => 'Du er arrangør', 'finalized' => 'Dagen er fundet', 'closed' => 'Afstemningen er lukket'][$poll->status] }}</p>
        <h1 id="manage-heading" class="compact-heading poll-title">{{ $poll->title }}</h1>
        @if (session('status'))
            <p class="management-feedback" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div class="management-error" role="alert">{{ $errors->first() }}</div>
        @endif

        @if ($poll->finalOption)
            <div class="final-date-banner">
                <p class="eyebrow">Så er dagen fundet ✓</p>
                <h2>{{ $poll->finalDateLabel() }}</h2>
                <p>Datoen er valgt, og der kan ikke længere ændres svar.</p>
            </div>
        @elseif ($poll->status === 'closed')
            <p class="closed-notice">Afstemningen er lukket uden en endelig dato. Alle svar er bevaret.</p>
        @endif

        @if ($poll->status === 'open')
            <h2 class="management-heading">Hvilken dag vælger I?</h2>
            <p class="result-count">{{ $results['count'] }} har svaret @if ($results['incomplete']) · {{ $results['incomplete'] }} mangler at svare på alle datoer @endif</p>
            <form method="POST" action="{{ route('polls.manage.update', [$poll, 'finalize']) }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <input type="hidden" name="version" value="{{ $poll->management_version }}">
                <fieldset class="final-options">
                    <legend class="sr-only">Vælg endelig dato</legend>
                    @foreach ($results['options'] as $option)
                        <label class="final-option">
                            <input type="radio" name="option_id" value="{{ $option['id'] }}" required>
                            <span>
                                @if ($option['best'])
                                    <span class="best-label">{{ collect($results['options'])->where('best', true)->count() > 1 ? 'Delt bedste mulighed' : 'Passer bedst lige nu' }}</span>
                                @endif
                                <strong>{{ $option['label'] }}</strong>
                                <span class="hint">{{ $option['can'] }} kan · {{ $option['maybe'] }} måske · {{ $option['cannot'] }} kan ikke</span>
                                @if ($option['unanswered']) <span class="hint">{{ $option['unanswered'] }} ubesvaret</span> @endif
                            </span>
                        </label>
                    @endforeach
                </fieldset>
                <button type="submit" class="button primary" :disabled="busy">Vælg endelig dato <span aria-hidden="true">→</span></button>
                <p class="hint centered">Valget lukker for nye og ændrede svar. Du kan genåbne bagefter.</p>
            </form>

            <details class="management-section" open>
                <summary>Tilføj eller fjern datoer</summary>
                <form class="add-date-form" method="POST" action="{{ route('polls.manage.update', [$poll, 'add']) }}" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <input type="hidden" name="version" value="{{ $poll->management_version }}">
                    <label for="new-date">En ekstra mulighed</label>
                    <input type="date" id="new-date" name="date" required value="{{ old('date') }}">
                    <button type="submit" class="button secondary" :disabled="busy">Tilføj dato</button>
                    <p class="hint">En ny dato står som ubesvaret for alle, der allerede har svaret.</p>
                </form>
                <ul class="manage-dates">
                    @foreach ($poll->options as $option)
                        <li>
                            <div class="manage-date-label">
                                <strong>{{ ucfirst($option->date_value->locale('da')->translatedFormat('D j. F Y')) }}</strong>
                                <span class="hint">{{ $option->responses_count }} svar</span>
                            </div>
                            <form method="POST" action="{{ route('polls.manage.update', [$poll, 'remove']) }}" x-data="{ busy: false }" @submit="busy = true">
                                @csrf
                                <input type="hidden" name="version" value="{{ $poll->management_version }}">
                                <input type="hidden" name="option_id" value="{{ $option->id }}">
                                @if ($option->responses_count > 0 && $poll->options->count() > 2)
                                    <details class="remove-confirmation">
                                        <summary>Fjern dato</summary>
                                        <p class="hint">Datoen har {{ $option->responses_count }} svar. De bevares i historikken, men tæller ikke længere med i resultatet.</p>
                                        <label class="confirmation-label"><input type="checkbox" name="confirmed" value="1" required> Jeg vil fjerne denne dato</label>
                                        <button type="submit" class="text-button remove-button" :disabled="busy">Bekræft fjernelse</button>
                                    </details>
                                @else
                                    <button type="submit" class="text-button remove-button" :disabled="busy || {{ $poll->options->count() <= 2 ? 'true' : 'false' }}" @disabled($poll->options->count() <= 2)>Fjern dato</button>
                                @endif
                            </form>
                        </li>
                    @endforeach
                </ul>
                @if ($poll->options->count() <= 2) <p class="hint">Der skal være mindst to datoer. Tilføj en ny, før du fjerner en af de nuværende.</p> @endif
            </details>
        @else
            <form method="POST" action="{{ route('polls.manage.update', [$poll, 'reopen']) }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <input type="hidden" name="version" value="{{ $poll->management_version }}">
                <button type="submit" class="button primary" :disabled="busy">Genåbn afstemningen</button>
                <p class="hint centered">Svarene bevares. En tidligere valgt dato er ikke længere endelig.</p>
            </form>
        @endif

        @if (in_array($poll->status, ['open', 'finalized']))
            <details class="management-section close-section">
                <summary>Luk afstemningen</summary>
                <p class="hint">Der kan ikke længere svares. Svarene{{ $poll->final_option_id ? ' og den valgte dato' : '' }} bevares, og du kan genåbne senere.</p>
                <form method="POST" action="{{ route('polls.manage.update', [$poll, 'close']) }}" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <input type="hidden" name="version" value="{{ $poll->management_version }}">
                    <button type="submit" class="button secondary" :disabled="busy">Luk {{ $poll->status === 'open' ? 'uden at vælge dato' : 'afstemningen' }}</button>
                </form>
            </details>
        @endif
    </section>
</x-layout>
