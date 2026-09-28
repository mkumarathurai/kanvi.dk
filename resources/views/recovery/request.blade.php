<x-layout title="Find din arrangøradgang" :private="true">
    <section class="flow" aria-labelledby="recovery-heading">
        <p class="eyebrow">Find tilbage</p>
        <h1 class="compact-heading" id="recovery-heading">Mistet arrangøradgangen?</h1>
        <p class="lead">{{ $poll->title }}</p>
        <p>Brug den mailadresse, du tidligere har bekræftet til afstemningen.</p>
        @if (session('recovery_status')) <p class="management-feedback" role="status">{{ session('recovery_status') }}</p> @endif
        @if ($mailEnabled)
            <form method="POST" action="{{ route('recovery.send', $poll) }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <label for="recovery-email">Din mailadresse</label>
                <input id="recovery-email" type="email" name="email" required maxlength="254" autocomplete="email" value="{{ old('email') }}">
                @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                <button class="button primary" :disabled="busy">Send nyt adgangslink →</button>
            </form>
        @else
            <p class="closed-notice">Mail er ikke slået til endnu. Brug dit gemte administrationslink eller den browser, du oprettede afstemningen i.</p>
        @endif
        <p class="hint">Har du hverken browseradgang, administrationslink eller en bekræftet mailadresse, kan vi ikke genskabe din adgang.</p>
        <a class="text-link" href="{{ route('polls.show', $poll) }}">← Tilbage til afstemningen</a>
    </section>
</x-layout>
