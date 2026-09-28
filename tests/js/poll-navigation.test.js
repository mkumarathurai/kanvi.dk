import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = {};
await import('../../resources/js/poll.js');
const initial = { participant: null, isOpen: true, canSeeResults: false, optionIds: ['date1', 'date2'], saveUrl: '/responses', resultsUrl: '/results' };

test('participant navigation respects result access and does not submit answers', () => {
    const editor = window.kanviPoll(initial);
    assert.equal(editor.screen, 'intro');
    editor.goTo('results');
    editor.goTo('confirmation');
    assert.equal(editor.screen, 'intro');
    editor.goTo('answers');
    assert.equal(editor.screen, 'answers');
    assert.equal(editor.busy, false);
    assert.deepEqual(editor.pending, {});
    editor.dispose();

    const closed = window.kanviPoll({ ...initial, isOpen: false });
    assert.equal(closed.screen, 'results');
    assert.equal(closed.canSeeResults, false);
    closed.dispose();
});

test('confirmation requires ACK of the latest choice and permits partial answers', async () => {
    const originalFetch = globalThis.fetch;
    const requests = [];
    globalThis.fetch = (url, options) => {
        if (url === '/results') return Promise.resolve(new Response(JSON.stringify({ status: 'open', options: [{ id: 'date1' }, { id: 'date2' }] })));
        return new Promise(resolve => requests.push({ resolve, body: JSON.parse(options.body) }));
    };
    const editor = window.kanviPoll(initial);
    const ack = (index, answer) => requests[index].resolve(new Response(JSON.stringify({
        participant: { name: 'Mette', answers: { date1: answer } },
        acknowledged: requests[index].body.changes.map(({ field, revision }) => ({ field, revision })),
    })));
    try {
        editor.goTo('answers');
        editor.changeName('Mette');
        const saving = editor.choose('date1', 'can');
        editor.goTo('confirmation');
        assert.equal(editor.screen, 'answers');
        editor.choose('date1', 'maybe');
        ack(0, 'can');
        await saving;
        editor.goTo('confirmation');
        assert.equal(editor.screen, 'answers', 'A stale ACK must not unlock the saved screen');
        const latest = editor.flush();
        ack(1, 'maybe');
        await latest;
        editor.goTo('confirmation');
        assert.equal(editor.screen, 'confirmation');
        assert.equal(editor.confirmedCount(), 1);
        assert.equal(editor.answers.date2, undefined);
        editor.goTo('results');
        assert.equal(editor.screen, 'results');
        editor.goTo('answers');
        assert.equal(editor.answers.date1, 'maybe');
        assert.equal(requests.length, 2, 'Navigating never submits an extra save');
    } finally {
        editor.dispose();
        globalThis.fetch = originalFetch;
    }
});
