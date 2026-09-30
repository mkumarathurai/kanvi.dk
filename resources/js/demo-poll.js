// An isolated playground: no fetch, storage, participant identity or autosave.
const baseAnswers = [
    ['can', 'can', 'maybe', 'can', 'cannot'],
    ['can', 'maybe', 'cannot', 'can', 'maybe'],
    ['maybe', 'can', 'can', 'maybe', 'cannot'],
    ['can', 'can', 'cannot', 'can', 'can'],
    ['cannot', 'can', 'can', 'maybe', 'can'],
];
const names = ['Mette', 'Jens', 'Louise', 'Peter', 'Katrine'];
const dates = ['Fre. 9. okt.', 'Lør. 10. okt.', 'Fre. 16. okt.', 'Lør. 17. okt.', 'Søn. 18. okt.'];

export function createDemoPoll() {
    return {
        answers: [null, null, null, null, null],
        expanded: false,
        labels: { can: 'Kan', maybe: 'Måske', cannot: 'Kan ikke', unanswered: 'Ubesvaret' },
        get hasAnswers() { return this.answers.some(answer => answer !== null); },
        get people() {
            const people = baseAnswers.map((answers, id) => ({ id, name: names[id], answers }));
            if (this.hasAnswers) people.push({ id: 'visitor', name: 'Dig', answers: this.answers });
            return people;
        },
        get results() {
            return {
                count: this.people.length,
                options: dates.map((label, id) => ({
                    id, label,
                    ...Object.fromEntries(Object.keys(this.labels).map(value => [value, this.count(id, value)])),
                    people: this.people.map(person => ({ id: person.id, name: person.name, value: person.answers[id] ?? 'unanswered' })),
                })),
            };
        },
        choose(index, value) {
            if (Number.isInteger(index) && index >= 0 && index < 5 && ['can', 'maybe', 'cannot'].includes(value)) {
                this.answers[index] = value;
            }
        },
        count(index, value) {
            return this.people.filter(person => (person.answers[index] ?? 'unanswered') === value).length;
        },
        canCount(index) { return this.count(index, 'can'); },
        score(index) { return this.count(index, 'can') * 100 + this.count(index, 'maybe') * 10 - this.count(index, 'cannot'); },
        isBest(index) { return this.score(index) === Math.max(...this.answers.map((_, i) => this.score(i))); },
        reset() { this.answers = [null, null, null, null, null]; },
    };
}
