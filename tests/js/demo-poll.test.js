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
