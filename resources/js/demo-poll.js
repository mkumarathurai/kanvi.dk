// An isolated playground: no fetch, storage, participant identity or autosave.
const baseAnswers = [
    ['can', 'can', 'maybe', 'can', 'cannot'],
    ['can', 'maybe', 'cannot', 'can', 'maybe'],
    ['maybe', 'can', 'can', 'maybe', 'cannot'],
    ['can', 'can', 'cannot', 'can', 'can'],
    ['cannot', 'can', 'can', 'maybe', 'can'],
];

export function createDemoPoll() {
    return {
        answers: [null, null, null, null, null],
        expanded: false,
        get hasAnswers() { return this.answers.some(answer => answer !== null); },
        choose(index, value) {
            if (Number.isInteger(index) && index >= 0 && index < 5 && ['can', 'maybe', 'cannot'].includes(value)) {
                this.answers[index] = value;
            }
        },
        count(index, value) {
            return baseAnswers.filter(answers => answers[index] === value).length + Number(this.answers[index] === value);
        },
        canCount(index) { return this.count(index, 'can'); },
        score(index) { return this.count(index, 'can') * 100 + this.count(index, 'maybe') * 10 - this.count(index, 'cannot'); },
        isBest(index) { return this.score(index) === Math.max(...this.answers.map((_, i) => this.score(i))); },
        reset() { this.answers = [null, null, null, null, null]; },
    };
}
