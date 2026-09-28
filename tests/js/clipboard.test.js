import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = {};
await import('../../resources/js/app.js');
const copyLink = window.kanviCopy;

function fixture(clipboardApi) {
    let clipboard = 'Tidligere indhold';
    let selection = '';
    const button = { focus() { document.activeElement = button; } };
    const document = {
        activeElement: button,
        execCommand(command) {
            assert.equal(command, 'copy');
            clipboard = selection;
            return true;
        },
    };
    const input = {
        value: 'http://kanvi.dk.test/p/test-poll',
        ownerDocument: document,
        focus() { document.activeElement = input; },
        select() { selection = input.value; },
        setSelectionRange(start, end) { selection = input.value.slice(start, end); },
    };
    Object.defineProperty(globalThis, 'navigator', {
        configurable: true,
        value: clipboardApi ? { clipboard: { writeText: text => clipboardApi(text, value => { clipboard = value; }) } } : {},
    });
    globalThis.document = document;
    return { input, document, button, clipboard: () => clipboard };
}

test('copy button copies the link when Clipboard API is unavailable on HTTP', async () => {
    const f = fixture();
    const notice = await copyLink(f.input);
    assert.equal(f.clipboard(), f.input.value);
    assert.equal(notice, 'Linket er kopieret.');
    assert.equal(f.document.activeElement, f.button);
});

test('copy uses Clipboard API when available', async () => {
    const f = fixture(async (text, write) => write(text));
    f.document.execCommand = () => assert.fail('Fallback must not run');
    assert.equal(await copyLink(f.input), 'Linket er kopieret.');
    assert.equal(f.clipboard(), f.input.value);
});

test('copy falls back after Clipboard API rejection', async () => {
    const f = fixture(async () => { throw new Error('NotAllowedError'); });
    assert.equal(await copyLink(f.input), 'Linket er kopieret.');
    assert.equal(f.clipboard(), f.input.value);
});

for (const failure of ['false', 'throw']) {
    test(`failed fallback (${failure}) never claims the link was copied`, async () => {
        const f = fixture();
        f.document.execCommand = () => {
            if (failure === 'throw') throw new Error('Blocked');
            return false;
        };
        assert.match(await copyLink(f.input), /Kunne ikke kopiere automatisk/);
        assert.equal(f.clipboard(), 'Tidligere indhold');
        assert.equal(f.document.activeElement, f.input);
    });
}
