import test from 'node:test';
import assert from 'node:assert/strict';
import { createDemoPoll } from '../../resources/js/demo-poll.js';

test('demo replaces a choice rather than counting repeat clicks as participants', () => {
    const demo = createDemoPoll();
    assert.equal(demo.canCount(0), 3);
    demo.choose(0, 'can');
    demo.choose(0, 'can');
    assert.equal(demo.canCount(0), 4);
    demo.choose(0, 'cannot');
    assert.equal(demo.canCount(0), 3);
    assert.equal(demo.count(0, 'cannot'), 2);
    assert.equal(demo.count(1, 'cannot'), 0);
    assert.equal(demo.answers[1], null);
    demo.reset();
    assert.equal(demo.hasAnswers, false);
    assert.equal(demo.count(0, 'cannot'), 1);
});

test('separate playgrounds do not share visitor choices and malformed choices are ignored', () => {
    const first = createDemoPoll();
    first.choose(0, 'can');
    const second = createDemoPoll();
    second.choose(-1, 'can');
    second.choose(5, 'can');
    second.choose(1, 'invalid');
    assert.deepEqual(second.answers, [null, null, null, null, null]);
    assert.equal(second.canCount(0), 3);
    assert.equal(first.canCount(0), 4);
});

test('visible participant answers and totals agree before, during and after a trial response', () => {
    const demo = createDemoPoll();
    const checkTotals = () => {
        const { count, options } = demo.results;
        for (const option of options) {
            assert.equal(option.people.length, count);
            for (const value of ['can', 'maybe', 'cannot', 'unanswered']) {
                assert.equal(option[value], option.people.filter(person => person.value === value).length);
            }
            assert.equal(option.can + option.maybe + option.cannot + option.unanswered, count);
        }
    };
    assert.equal(demo.results.count, 5);
    checkTotals();
    demo.choose(0, 'maybe');
    assert.equal(demo.results.count, 6);
    assert.equal(demo.results.options[0].people.at(-1).name, 'Dig');
    assert.equal(demo.results.options[0].people.at(-1).value, 'maybe');
    assert.equal(demo.results.options[1].unanswered, 1);
    checkTotals();
    demo.choose(0, 'cannot');
    demo.choose(1, 'can');
    checkTotals();
    demo.reset();
    assert.equal(demo.results.count, 5);
    assert.equal(demo.results.options[1].unanswered, 0);
    checkTotals();
});

test('changing an answer updates the best date and preserves exact ties', () => {
    const demo = createDemoPoll();
    assert.deepEqual([0, 1, 2, 3, 4].filter(index => demo.isBest(index)), [1]);
    demo.choose(0, 'can');
    assert.deepEqual([0, 1, 2, 3, 4].filter(index => demo.isBest(index)), [1]);
    demo.choose(1, 'cannot');
    assert.deepEqual([0, 1, 2, 3, 4].filter(index => demo.isBest(index)), [0, 1]);
    demo.choose(3, 'can');
    assert.deepEqual([0, 1, 2, 3, 4].filter(index => demo.isBest(index)), [3]);
});
