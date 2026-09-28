import test from 'node:test';
import assert from 'node:assert/strict';
import { createResponseEditor } from '../../resources/js/response-editor.js';

function deferred() {
    let resolve, reject;
    const promise = new Promise((a, b) => { resolve = a; reject = b; });
    return { promise, resolve, reject };
}
function fixture(participant = null) {
    const requests = [], timers = new Map();
    let timerId = 0;
    const editor = createResponseEditor({ participant, canSeeResults: Boolean(participant), isOpen: true }, {
        editorId: 'a'.repeat(32),
        send: body => { const task = deferred(); requests.push({ body, ...task }); return task.promise; },
        schedule: (callback, delay) => { timers.set(++timerId, { callback, delay }); return timerId; },
        cancel: id => timers.delete(id),
    });
    const ack = (index, name, answers) => requests[index].resolve({
        acknowledged: requests[index].body.changes.map(({ field, revision }) => ({ field, revision })),
        participant: { name, answers },
    });
    const runNext = () => {
        const [id, timer] = timers.entries().next().value;
        timers.delete(id);
        return timer.callback();
    };
    return { editor, requests, timers, ack, runNext };
}

test('name alone remains idle and does not submit or unlock results', () => {
    const { editor, requests, timers } = fixture();
    editor.changeName('Mathi');
    assert.equal(editor.state, 'idle');
    assert.equal(requests.length, 0);
    assert.equal(timers.size, 0);
    assert.equal(editor.canSeeResults, false);
});

test('first answer is local immediately, but saved/result access require ACK', async () => {
    const { editor, requests, ack } = fixture();
    editor.changeName('Mathi');
    const saving = editor.choose('date1', 'can');
    assert.equal(editor.answers.date1, 'can');
    assert.equal(editor.confirmed, null);
    assert.equal(editor.state, 'saving');
    assert.equal(editor.canSeeResults, false);
    assert.equal(requests[0].body.changes.length, 2);
    ack(0, 'Mathi', { date1: 'can' });
    await saving;
    assert.equal(editor.state, 'saved');
    assert.equal(editor.canSeeResults, true);
});

test('rapid Can → Maybe → Can cannot be marked saved by the first ACK', async () => {
    const { editor, requests, ack, runNext } = fixture({ name: 'Mathi', answers: {} });
    const first = editor.choose('date1', 'can');
    editor.choose('date1', 'maybe');
    editor.choose('date1', 'can');
    assert.equal(requests.length, 1);
    ack(0, 'Mathi', { date1: 'can' });
    await first;
    assert.equal(editor.answers.date1, 'can');
    assert.equal(editor.state, 'saving');
    const latest = runNext();
    assert.equal(requests[1].body.changes[0].revision, 3);
    ack(1, 'Mathi', { date1: 'can' });
    await latest;
    assert.equal(editor.state, 'saved');
});

test('old server value never overwrites a newer local choice', async () => {
    const { editor, ack } = fixture({ name: 'Mathi', answers: {} });
    const first = editor.choose('date1', 'can');
    editor.choose('date1', 'cannot');
    ack(0, 'Mathi', { date1: 'can' });
    await first;
    assert.equal(editor.answers.date1, 'cannot');
    assert.equal(editor.confirmed.answers.date1, 'can');
    assert.equal(editor.state, 'saving');
});

test('network errors retain local choices and retry with same revisions and backoff', async () => {
    const { editor, requests, timers, runNext, ack } = fixture();
    editor.changeName('Mathi');
    const saving = editor.choose('date1', 'maybe');
    requests[0].reject(new TypeError('offline'));
    await saving;
    assert.equal(editor.state, 'error');
    assert.equal(editor.answers.date1, 'maybe');
    assert.equal(editor.confirmed, null);
    assert.equal(editor.retryScheduled, true);
    assert.equal([...timers.values()][0].delay, 1000);
    const retry = runNext();
    assert.deepEqual(requests[1].body, requests[0].body);
    requests[1].reject(new TypeError('offline'));
    await retry;
    assert.equal([...timers.values()][0].delay, 2000);
    const recovery = editor.retry();
    assert.equal(timers.size, 0);
    ack(2, 'Mathi', { date1: 'maybe' });
    await recovery;
    assert.equal(editor.state, 'saved');
    assert.equal(editor.retryScheduled, false);
});

test('editing during backoff cancels stale retry and sends the latest selection', async () => {
    const { editor, requests, timers, ack } = fixture({ name: 'Mathi', answers: {} });
    const first = editor.choose('date1', 'can');
    requests[0].reject(new Error('offline'));
    await first;
    const latest = editor.choose('date1', 'cannot');
    assert.equal(timers.size, 0);
    assert.equal(requests[1].body.changes[0].value, 'cannot');
    ack(1, 'Mathi', { date1: 'cannot' });
    await latest;
    assert.equal(editor.state, 'saved');
});

test('closing during save preserves unsaved local choice and stops retries', async () => {
    const { editor, requests, timers } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    requests[0].reject(Object.assign(new Error('Afstemningen er lukket.'), { status: 409 }));
    await saving;
    assert.equal(editor.state, 'error');
    assert.equal(editor.answers.date1, 'can');
    assert.equal(editor.confirmed.answers.date1, undefined);
    assert.equal(editor.isOpen, false);
    assert.equal(timers.size, 0);
    editor.retry();
    assert.equal(requests.length, 1);
});

test('validation and authorization failures never retry indefinitely', async () => {
    for (const status of [403, 419, 422]) {
        const { editor, requests, timers } = fixture({ name: 'Mathi', answers: {} });
        const saving = editor.choose('date1', 'can');
        requests[0].reject(Object.assign(new Error('Afvist'), { status }));
        await saving;
        assert.equal(editor.state, 'error');
        assert.equal(editor.retryable, false);
        assert.equal(timers.size, 0);
    }
});

test('answers without a name are retained and saved together after entering a name', async () => {
    const { editor, requests, ack, runNext } = fixture();
    editor.choose('date1', 'can');
    assert.equal(editor.state, 'error');
    assert.equal(requests.length, 0);
    editor.changeName('Mathi');
    const saving = runNext();
    ack(0, 'Mathi', { date1: 'can' });
    await saving;
    assert.equal(editor.state, 'saved');
});

test('replayed ACK with a newer value from another tab adopts the actual server state', async () => {
    const { editor, ack } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    ack(0, 'Mathi', { date1: 'maybe' });
    await saving;
    assert.equal(editor.answers.date1, 'maybe');
    assert.equal(editor.state, 'saved');
    assert.match(editor.notice, /anden fane/);
});

test('missing ACK is not a successful save', async () => {
    const { editor, requests } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    requests[0].resolve({ participant: { name: 'Mathi', answers: { date1: 'can' } }, acknowledged: [] });
    await saving;
    assert.equal(editor.state, 'error');
    assert.equal(editor.confirmed.answers.date1, undefined);
});

test('destroying the editor cancels retries and ignores late ACKs', async () => {
    const { editor, requests, ack, timers } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    editor.dispose();
    ack(0, 'Mathi', { date1: 'can' });
    await saving;
    assert.equal(editor.confirmed.answers.date1, undefined);
    assert.equal(timers.size, 0);
    await editor.flush();
    assert.equal(requests.length, 1);
});

test('browser adapter keeps reactive save state and result failures separate', async () => {
    const originalWindow = globalThis.window;
    const originalFetch = globalThis.fetch;
    globalThis.window = {};
    try {
        await import('../../resources/js/poll.js');
        const request = deferred();
        let submitted;
        globalThis.fetch = async (url, options) => {
            if (url.endsWith('/results')) throw new Error('Result request failed');
            submitted = JSON.parse(options.body);
            assert.equal(options.credentials, 'same-origin');
            assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf-test-token');
            return request.promise;
        };
        const editor = window.kanviPoll({ participant: null, canSeeResults: false, isOpen: true, saveUrl: '/responses', resultsUrl: '/results', csrf: 'csrf-test-token' });
        editor.changeName('Mathi');
        const saving = editor.choose('date1', 'can');
        assert.equal(editor.state, 'saving');
        request.resolve(new Response(JSON.stringify({
            acknowledged: submitted.changes.map(({ field, revision }) => ({ field, revision })),
            participant: { name: 'Mathi', answers: { date1: 'can' } },
        }), { status: 200 }));
        await saving;
        await new Promise(setImmediate);
        assert.equal(editor.state, 'saved');
        assert.equal(editor.statusText, '✓ Gemt');
        assert.match(editor.resultError, /Kunne ikke hente/);
        assert.equal(editor.error, '');
        editor.dispose();
    } finally {
        globalThis.window = originalWindow;
        globalThis.fetch = originalFetch;
    }
});

test('result refresh closing a poll cancels retries and retains unconfirmed changes', async () => {
    const { editor, requests, timers } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    requests[0].reject(new Error('Offline'));
    await saving;
    assert.equal(timers.size, 1);
    editor.updateAvailability(false);
    assert.equal(timers.size, 0);
    assert.equal(editor.retryable, false);
    assert.equal(editor.state, 'error');
    assert.match(editor.statusText, /ikke er bekræftet gemt/);
    assert.equal(editor.answers.date1, 'can');
    await editor.retry();
    assert.equal(requests.length, 1);
});

test('an ACK after a closing result refresh confirms only the submitted version', async () => {
    for (const newerChoice of [false, true]) {
        const { editor, ack, timers } = fixture({ name: 'Mathi', answers: {} });
        const saving = editor.choose('date1', 'can');
        if (newerChoice) editor.choose('date1', 'maybe');
        editor.updateAvailability(false);
        ack(0, 'Mathi', { date1: 'can' });
        await saving;
        assert.equal(editor.state, newerChoice ? 'error' : 'saved');
        assert.equal(editor.answers.date1, newerChoice ? 'maybe' : 'can');
        assert.equal(editor.confirmed.answers.date1, 'can');
        assert.equal(timers.size, 0);
    }
});

test('a network error after a closing result refresh does not restart retries', async () => {
    const { editor, requests, timers } = fixture({ name: 'Mathi', answers: {} });
    const saving = editor.choose('date1', 'can');
    editor.updateAvailability(false);
    requests[0].reject(new Error('Offline'));
    await saving;
    assert.equal(editor.state, 'error');
    assert.equal(editor.retryable, false);
    assert.equal(timers.size, 0);
});
