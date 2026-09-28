import { createResponseEditor } from './response-editor.js';

async function jsonRequest(url, options = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: { Accept: 'application/json', ...options.headers },
            signal: controller.signal,
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) {
            const messages = {
                403: 'Du har ikke adgang til at ændre disse svar. Genindlæs siden.',
                404: 'Afstemningen findes ikke længere.',
                419: 'Browseradgangen er udløbet. Genindlæs siden, og prøv igen.',
                409: 'Afstemningen er lukket for svar. Din ændring blev ikke gemt.',
                422: Object.values(body.errors ?? {}).flat()[0] || body.message || 'Kontrollér dit navn og dine svar.',
            };
            throw Object.assign(new Error(messages[response.status] || 'Kunne ikke gemme lige nu.'), { status: response.status });
        }
        return body;
    } finally {
        clearTimeout(timeout);
    }
}

window.kanviPoll = (initial) => {
    const editorId = Array.from(crypto.getRandomValues(new Uint8Array(16)), n => n.toString(16).padStart(2, '0')).join('');
    const editor = createResponseEditor(initial, {
            editorId,
            send: body => jsonRequest(initial.saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': initial.csrf },
                body: JSON.stringify(body),
            }),
        });
    return Object.assign(editor, {
        results: null,
        finalDate: initial.finalDate ?? null,
        optionsChanged: false,
        resultError: '',
        loadingResults: false,
        resultRequest: 0,
        labels: { can: 'Kan', maybe: 'Måske', cannot: 'Kan ikke', unanswered: 'Ubesvaret' },
        init() {
            if (this.canSeeResults) this.refreshResults();
            this.onlineHandler = () => { if (this.retryable) this.retry(); };
            this.unloadHandler = event => {
                if (!Object.keys(this.pending).length) return;
                event.preventDefault();
                event.returnValue = '';
            };
            window.addEventListener('online', this.onlineHandler);
            window.addEventListener('beforeunload', this.unloadHandler);
        },
        onConfirmed() {
            if (this.canSeeResults) return this.refreshResults();
        },
        async refreshResults() {
            const request = ++this.resultRequest;
            this.loadingResults = true;
            this.resultError = '';
            try {
                const results = await jsonRequest(initial.resultsUrl);
                if (request === this.resultRequest && !this.destroyed) {
                    this.results = results;
                    this.finalDate = results.final_date ?? null;
                    this.updateAvailability(results.status === 'open');
                    this.optionsChanged = [...(initial.optionIds ?? [])].sort().join(',') !== results.options.map(option => option.id).sort().join(',');
                }
            } catch (error) {
                if (request !== this.resultRequest || this.destroyed) return;
                if ([403, 404].includes(error.status)) {
                    this.results = null;
                    this.canSeeResults = false;
                }
                this.resultError = 'Kunne ikke hente resultatet. Dine gemte svar er stadig gemt.';
            } finally {
                if (request === this.resultRequest) this.loadingResults = false;
            }
        },
        destroy() {
            this.dispose();
            window.removeEventListener('online', this.onlineHandler);
            window.removeEventListener('beforeunload', this.unloadHandler);
        },
    });
};
