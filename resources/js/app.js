import './poll.js';

window.kanviCopy = async (input) => {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(input.value);
            return 'Linket er kopieret.';
        } catch {
            // Try selection-based copying if permission to use Clipboard API is denied.
        }
    }

    const document = input.ownerDocument;
    const previousFocus = document.activeElement;
    input.focus({ preventScroll: true });
    input.select();
    input.setSelectionRange(0, input.value.length);
    try {
        // Compatibility fallback for HTTP and browsers without Clipboard API.
        // Keep this synchronous with the click when Clipboard API is unavailable.
        if (document.execCommand('copy')) {
            previousFocus?.focus({ preventScroll: true });
            return 'Linket er kopieret.';
        }
    } catch {
        // Leave the link selected so manual copying remains possible.
    }
    return 'Kunne ikke kopiere automatisk. Linket er markeret – vælg Kopiér eller tryk Ctrl+C / ⌘C.';
};

window.kanviShare = async (url, title) => {
    try {
        await navigator.share({ title, text: 'Find en dag, der passer gruppen.', url });
        return '';
    } catch (error) {
        return error.name === 'AbortError' ? '' : 'Kunne ikke dele. Kopiér linket i stedet.';
    }
};
