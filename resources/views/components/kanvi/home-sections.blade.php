<div class="home-sections">
    <section class="occasions-section" aria-labelledby="occasions-heading">
        <div class="section-heading">
            <div><p class="section-kicker">Kan vi …?</p><h2 id="occasions-heading">Der er altid noget, der skal passe sammen.</h2><p class="section-lead">Kanvi kan bruges til alt, hvor I skal finde en dag – stort som småt.</p></div>
            <p class="hand-note occasions-note" aria-hidden="true">Samme nemme løsning,<br>uanset anledningen.<span>↘</span></p>
        </div>
        <ul class="occasion-grid">
            @foreach ([['party', 'Sommerfesten', 'Hvornår kan naboerne?', 'Sommerfest med naboerne'], ['badminton', 'Badminton', 'Hvornår kan holdet spille?', 'Badminton med holdet'], ['dinner', 'Middag med vennerne', 'Hvornår kan alle?', 'Middag med vennerne'], ['cake', 'Fødselsdagen', 'Hvilken weekend passer familien?', 'Fødselsdag med familien'], ['house', 'Bestyrelsesmødet', 'Hvornår kan bestyrelsen mødes?', 'Bestyrelsesmøde'], ['tree', 'Julefrokosten', 'Find dagen uden 48 beskeder.', 'Julefrokost']] as [$art, $label, $description, $title])
                <li><a class="occasion-card" href="{{ route('polls.create', ['title' => $title]) }}" aria-label="Opret afstemning: {{ $title }}"><x-kanvi.occasion-art :name="$art" /><h3>{{ $label }}</h3><p>{{ $description }}</p><span class="occasion-arrow" aria-hidden="true">↗</span></a></li>
            @endforeach
        </ul>
    </section>

    <section class="sharing-section" aria-labelledby="sharing-heading">
        <div class="sharing-copy">
            <p class="section-kicker">Ingen app. Ingen konto. Bare et link.</p>
            <h2 id="sharing-heading">Del på den måde, I allerede bruger.</h2>
            <p class="section-lead">Du opretter afstemningen og deler linket dér, hvor I allerede taler sammen. De andre åbner linket, svarer – og så finder I dagen.</p>
            <ul class="home-share-channels" aria-label="Del afstemningslinket via">
                <li><span class="channel-icon channel-whatsapp"><x-kanvi.icon name="phone" :size="26" /></span><span>WhatsApp</span></li>
                <li><span class="channel-icon channel-messenger"><x-kanvi.icon name="chat" :size="26" /></span><span>Messenger</span></li>
                <li><span class="channel-icon channel-sms"><x-kanvi.icon name="chat" :size="26" /></span><span>SMS</span></li>
                <li><span class="channel-icon channel-mail"><x-kanvi.icon name="mail" :size="26" /></span><span>Mail</span></li>
                <li><span class="channel-icon"><x-kanvi.icon name="link" :size="26" /></span><span>Kopiér link</span></li>
                <li><span class="channel-icon channel-more" aria-hidden="true">•••</span><span>Eller noget<br>helt femte.</span></li>
            </ul>
        </div>
        <div class="sharing-illustration" role="img" aria-label="Du deler ét afstemningslink, og Mette, Jens og Louise svarer hver for sig.">
            <div class="sharing-organizer"><img src="{{ asset('images/home/organizer.png') }}" width="1280" height="1280" loading="lazy" alt=""><p><strong>Dig</strong><span>Opretter afstemning</span></p></div>
            <div class="illustrated-link"><x-kanvi.icon name="link" :size="20" /><span>kanvi.dk/…</span></div>
            <svg class="sharing-arrows" viewBox="0 0 500 280" fill="none" aria-hidden="true"><path d="M184 143h45m-8-6 8 6-8 6M305 140c15-38 26-67 56-70m-8-7 10 7-8 8m-50 64h58m-8-6 8 6-8 6m-50-2c14 32 27 66 58 70m-8-9 9 9-11 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m210 77 4-21m-18 34-9-12" stroke="#FBBF24" stroke-width="4" stroke-linecap="round"/></svg>
            <div class="sharing-replies" aria-hidden="true">
                @foreach ([['M', 'Mette', 'Kan fredag', 'mette'], ['J', 'Jens', 'Måske lørdag', 'jens'], ['L', 'Louise', 'Kan ikke søndag', 'louise']] as [$initial, $name, $answer, $avatar])
                    <div class="sharing-reply"><span class="example-avatar avatar-{{ $avatar }}">{{ $initial }}</span><p><strong>{{ $name }}</strong><span>{{ $answer }}</span></p></div>
                @endforeach
            </div>
            <p class="hand-note sharing-note" aria-hidden="true">Alle kan<br>være med.</p>
        </div>
    </section>

    <section class="try-section" id="proev-selv" aria-labelledby="try-heading">
        <div><p class="section-kicker">Prøv selv</p><h2 id="try-heading">Et eksempel fra virkeligheden.</h2><p class="section-lead">Klik og prøv – du kan ikke ødelægge noget.</p></div>
        <x-kanvi.demo-poll />
        <p class="hand-note try-note" aria-hidden="true">Det er præcis<br>så enkelt.<span>↙</span></p>
    </section>

    <section class="home-faq" id="spoergsmaal" aria-labelledby="faq-heading">
        <div><p class="section-kicker">Ofte stillede spørgsmål</p><h2 id="faq-heading">Har du <br>spørgsmål?</h2><p class="section-lead">Få hurtigt svar på det mest almindelige.</p><a class="text-link" href="{{ url('/faq') }}">Alle spørgsmål og svar →</a></div>
        <div class="faq-grid">
            @foreach ([['Skal jeg oprette en konto?', 'Nej. Du kan oprette en afstemning med det samme. Den browser, du opretter den i, får arrangøradgang. Gem administrationslinket, så du kan finde tilbage.'], ['Kan jeg ændre datoerne bagefter?', 'Ja. Som arrangør kan du tilføje og fjerne datoer, mens afstemningen er åben. Der skal altid være mindst to datoer at vælge imellem.'], ['Skal dem jeg inviterer have en konto?', 'Nej. De åbner linket, skriver deres navn og vælger Kan, Måske eller Kan ikke. Der er ingen app at installere.'], ['Kan deltagerne ændre deres svar?', 'Ja, mens afstemningen er åben. De åbner linket i den samme browser, som de svarede i. Det kræver, at browserdata ikke er blevet slettet.'], ['Koster det noget?', 'Nej. Det er gratis at oprette en afstemning og gratis at svare.'], ['Kan andre finde min afstemning på Google?', 'Kanvi beder søgemaskiner om ikke at vise afstemninger i søgeresultaterne. Alle med det offentlige link kan dog åbne afstemningen, så del det kun med dem, der skal være med.']] as [$question, $answer])
                <details><summary>{{ $question }}<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></summary><p>{{ $answer }}</p></details>
            @endforeach
        </div>
    </section>

    <x-kanvi.home-guides />
    <section class="home-closing" aria-labelledby="closing-heading">
        <div><h2 id="closing-heading">Skal vi finde en dag?</h2><p>Det tager omkring 30 sekunder.</p></div>
        <a class="button primary" href="{{ route('polls.create') }}">Opret en afstemning <span aria-hidden="true">→</span></a>
        <ul><li><x-kanvi.icon name="people" :size="18" />Ingen konto</li><li><x-kanvi.icon name="check" :size="18" />Gratis</li><li><x-kanvi.icon name="link" :size="18" />Del med et link</li></ul>
    </section>
    <div class="home-brand-signoff"><a href="{{ route('home') }}" aria-label="Kanvi — forsiden"><x-kanvi.logo /></a><p>Lidt mindre planlægning. Lidt mere sammen.</p><a href="{{ url('/faq') }}">Spørgsmål og svar</a></div>
</div>
