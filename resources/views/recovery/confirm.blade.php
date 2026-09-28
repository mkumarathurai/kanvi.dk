<x-layout title="Din arrangøradgang" :private="true">
    <section class="flow" aria-labelledby="confirm-heading">
        @if ($valid)
            <p class="eyebrow">Velkommen tilbage</p>
            <h1 class="compact-heading" id="confirm-heading">Åbn som arrangør.</h1>
            <p>Fortsæt for at bekræfte din mailadresse og give denne browser adgang til afstemningen.</p>
            <form method="POST" action="{{ route('recovery.redeem') }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <button class="button primary" :disabled="busy">Fortsæt til afstemningen →</button>
            </form>
        @else
            <h1 class="compact-heading" id="confirm-heading">Linket kan ikke bruges.</h1>
            <p>Det kan være udløbet eller allerede brugt. Åbn afstemningens offentlige link og vælg “Mistet arrangøradgangen?” for at få et nyt.</p>
        @endif
    </section>
</x-layout>
