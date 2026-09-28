<x-layout :title="$poll->title" :private="true">
    <section class="flow screen-card share-flow" x-data="{ notice: '', adminNotice: '' }" aria-labelledby="share-heading">
        <div class="share-illustration"><x-kanvi.calendar-sketch /></div>
        <h1 class="compact-heading" id="share-heading">Din afstemning er klar!</h1>
        <p class="share-lead">Del linket med dem, der skal svare.</p>
        <div class="poll-summary">
            <h2>{{ $poll->title }}</h2>
            <p class="hint">{{ $poll->options->count() }} mulige datoer</p>
        </div>
        <label for="public-url">Del dette link med gruppen</label>
        <div class="share-copy-row"><input class="link-input" id="public-url" x-ref="publicUrl" type="text" readonly value="{{ $publicUrl }}" @click="$el.select()">
        <button type="button" class="button primary" @click="notice = await window.kanviCopy($refs.publicUrl)">Kopier link</button></div>
        <div class="share-options">
            <a href="https://wa.me/?text={{ rawurlencode($poll->title.' — '.$publicUrl) }}" target="_blank" rel="noopener noreferrer"><x-kanvi.icon name="chat" /><span>WhatsApp</span></a>
            <a href="mailto:?subject={{ rawurlencode($poll->title) }}&amp;body={{ rawurlencode('Find en dag, der passer gruppen: '.$publicUrl) }}"><x-kanvi.icon name="mail" /><span>Mail</span></a>
            <button type="button" @click="notice = await window.kanviCopy($refs.publicUrl)"><x-kanvi.icon name="link" /><span>Kopiér link</span></button>
        </div>
        <button type="button" class="button secondary" x-cloak x-show="typeof navigator.share === 'function'" @click="notice = await window.kanviShare($refs.publicUrl.value, {{ Illuminate\Support\Js::from($poll->title) }})">Del…</button>
        <p class="hint status" role="status" x-text="notice"></p>
        <a class="button secondary" href="{{ route('polls.show', $poll) }}">Gå til afstemningen →</a>
        <a class="text-link" href="{{ route('polls.manage', $poll) }}">Administrér datoer og vælg dagen</a>

        <aside class="access-note">
            <h2><x-kanvi.icon name="check" />Denne browser kan administrere afstemningen.</h2>
            <p>Gem også administrationslinket, så du kan finde tilbage fra en anden browser.</p>
            <details>
                <summary>Vis mit administrationslink</summary>
                <div class="admin-link-content">
                    <label for="admin-url">Dit private administrationslink</label>
                    <input id="admin-url" class="link-input" x-ref="adminUrl" readonly value="{{ $adminUrl }}" @click="$el.select()">
                    <button type="button" class="button secondary" @click="adminNotice = await window.kanviCopy($refs.adminUrl)">Kopier administrationslink</button>
                    <p class="hint" role="status" x-text="adminNotice"></p>
                    <p class="hint">Alle med dette link kan administrere afstemningen. Gem det til dig selv.</p>
                </div>
            </details>
            <div class="recovery-section">
                <h2><x-kanvi.icon name="mail" />Gem adgangen i din mail.</h2>
                @if (session('recovery_status')) <p class="management-feedback" role="status">{{ session('recovery_status') }}</p> @endif
                @if ($recoveryEmail) <p class="hint">Bekræftet mail: {{ $recoveryEmail }}</p> @endif
                @if ($mailEnabled)
                    <p>Vil du være sikker på ikke at miste adgangen? Send et adgangslink til din mail og bekræft adressen.</p>
                    <form method="POST" action="{{ route('recovery.register', $poll) }}" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <label for="recovery-email">Din mailadresse</label>
                        <input id="recovery-email" type="email" name="email" maxlength="254" required autocomplete="email" value="{{ old('email', $recoveryEmail) }}">
                        @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                        <button class="button secondary" :disabled="busy">Send adgangslink til min mail</button>
                    </form>
                    <p class="hint">Adressen er først registreret, når du åbner linket og bekræfter. Det er valgfrit.</p>
                @else
                    <p class="hint">Mail er ikke slået til endnu. Gem administrationslinket, så du kan finde tilbage.</p>
                @endif
            </div>
            <p class="hint">Sletter du browserdata uden at have gemt administrationslinket eller bekræftet din mailadresse, kan vi ikke genskabe din adgang.</p>
        </aside>
    </section>
</x-layout>
