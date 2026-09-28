<section class="participant-overview" x-show="results.count > 0" aria-labelledby="people-heading">
    <h3 id="people-heading">Deltagere <span class="count" x-text="'(' + results.count + ')' "></span></h3>
    <div class="participant-scroll" tabindex="0" role="region" aria-label="Deltagernes svar på alle datoer. Rul vandret for at se flere datoer.">
        <table class="participant-table">
            <thead><tr><th scope="col">Navn</th><template x-for="option in results.options" :key="option.id"><th scope="col" x-text="option.label"></th></template></tr></thead>
            <tbody>
                <template x-for="person in (results.options[0]?.people ?? [])" :key="person.id">
                    <tr><th scope="row"><span class="participant-avatar" aria-hidden="true" x-text="person.name.slice(0, 1).toUpperCase()"></span><span x-text="person.name"></span></th>
                        <template x-for="option in results.options" :key="option.id">
                            <td x-data="{ value: null }" x-effect="value = option.people.find(item => item.id === person.id)?.value ?? 'unanswered'">
                                <span class="mini-answer" :class="value" aria-hidden="true" x-text="{ can: '✓', maybe: '−', cannot: '×', unanswered: '·' }[value]"></span>
                                <span class="sr-only" x-text="labels[value]"></span>
                            </td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</section>
