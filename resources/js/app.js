import './poll.js';

window.kanviCopy = async (input) => {
    try {
        await navigator.clipboard.writeText(input.value);
        return 'Linket er kopieret.';
    } catch {
        input.focus();
        input.select();
        return 'Markér og kopiér linket i feltet ovenfor.';
    }
};

window.kanviShare = async (url, title) => {
    try {
        await navigator.share({ title, text: 'Find en dag, der passer gruppen.', url });
        return '';
    } catch (error) {
        return error.name === 'AbortError' ? '' : 'Kunne ikke dele. Kopiér linket i stedet.';
    }
};
