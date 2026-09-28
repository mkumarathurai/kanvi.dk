// Autosave owns local choices separately from the last server-confirmed snapshot.
export function createResponseEditor(initial, dependencies) {
    const { send, schedule = setTimeout, cancel = clearTimeout, editorId } = dependencies;
    return {
        editorId,
        name: initial.participant?.name ?? '',
        answers: { ...(initial.participant?.answers ?? {}) },
        confirmed: initial.participant ?? null,
        hasParticipant: Boolean(initial.participant),
        isOpen: initial.isOpen,
        canSeeResults: initial.canSeeResults,
        pending: {},
        revision: 0,
        busy: false,
        retryTimer: null,
        retryScheduled: false,
        attempts: 0,
        error: '',
        retryable: false,
        notice: '',
        destroyed: false,

        get state() {
            if (this.error) return 'error';
            if (this.busy || (Object.keys(this.pending).length && this.ready())) return 'saving';
            return this.confirmed && !Object.keys(this.pending).length ? 'saved' : 'idle';
        },
        get statusText() {
            if (this.state === 'error') return this.error;
            if (this.state === 'saving') return 'Gemmer…';
            if (this.state === 'saved') return '✓ Gemt';
            return 'Dine valg gemmes automatisk.';
        },
        ready() {
            return this.name.trim().length > 0 && this.name.trim().length <= 80
                && (this.hasParticipant || Object.keys(this.answers).length > 0);
        },
        mark(field, value) {
            this.pending = { ...this.pending, [field]: { field, value, revision: ++this.revision } };
            this.error = '';
            this.attempts = 0;
            this.stopTimer();
        },
        changeName(value) {
            if (!this.isOpen) return;
            this.name = value;
            this.mark('name', value.trim());
            if (!value.trim() && Object.keys(this.answers).length) {
                this.error = 'Skriv dit navn, så vi kan gemme dine svar.';
                this.retryable = false;
            } else if (this.ready()) {
                this.retryTimer = schedule(() => this.flush(), 400);
            }
        },
        choose(field, value) {
            if (!this.isOpen) return;
            this.answers = { ...this.answers, [field]: value };
            if (!this.hasParticipant && !this.pending.name) this.mark('name', this.name.trim());
            this.mark(field, value);
            if (!this.ready()) {
                this.error = 'Skriv dit navn, så vi kan gemme dine svar.';
                this.retryable = false;
                return;
            }
            return this.flush();
        },
        stopTimer() {
            if (this.retryTimer !== null) cancel(this.retryTimer);
            this.retryTimer = null;
            this.retryScheduled = false;
        },
        updateAvailability(isOpen) {
            this.isOpen = isOpen;
            if (isOpen) return;
            this.stopTimer();
            this.retryable = false;
            this.error = Object.keys(this.pending).length
                ? 'Afstemningen er lukket. Du har lokale ændringer, som ikke er bekræftet gemt.'
                : '';
        },
        retry() {
            if (!this.isOpen || this.busy) return;
            this.stopTimer();
            return this.flush();
        },
        async flush() {
            if (this.destroyed || this.busy || !this.isOpen || !this.ready() || !Object.keys(this.pending).length) return;
            this.stopTimer();
            this.busy = true;
            this.error = '';
            this.retryable = false;
            const changes = Object.values(this.pending).map(change => ({ ...change }));
            try {
                const result = await send({ editor_id: this.editorId, changes });
                if (this.destroyed) return;
                if (!result.participant || typeof result.participant.name !== 'string' || !result.participant.answers
                    || !Array.isArray(result.acknowledged)
                    || !changes.every(change => result.acknowledged.some(ack => ack.field === change.field && ack.revision === change.revision))) {
                    throw new Error('Missing acknowledgement');
                }
                for (const change of changes) {
                    if (this.pending[change.field]?.revision !== change.revision) continue;
                    const actual = change.field === 'name' ? result.participant.name : result.participant.answers[change.field];
                    if (actual !== change.value) this.notice = 'Et nyere svar fra en anden fane er hentet.';
                    delete this.pending[change.field];
                }
                this.pending = { ...this.pending };
                this.confirmed = result.participant;
                this.hasParticipant = true;
                if (!this.pending.name) this.name = result.participant.name;
                const merged = { ...result.participant.answers };
                for (const [field, change] of Object.entries(this.pending)) {
                    if (field !== 'name') merged[field] = change.value;
                }
                this.answers = merged;
                this.canSeeResults = this.canSeeResults || Object.keys(result.participant.answers).length > 0;
                this.attempts = 0;
                if (!this.isOpen) this.updateAvailability(false);
                // Result loading has its own error state and must never turn a saved vote into a save error.
                Promise.resolve().then(() => this.onConfirmed?.()).catch(() => {});
            } catch (error) {
                if (this.destroyed) return;
                const transient = this.isOpen && (!error.status || error.status >= 500 || [408, 429].includes(error.status));
                this.retryable = transient;
                if (error.status === 409) this.isOpen = false;
                if (transient) {
                    this.error = 'Kunne ikke gemme · prøver igen…';
                    this.retryScheduled = true;
                    const delay = Math.min(1000 * (2 ** this.attempts++), 30000);
                    this.retryTimer = schedule(() => this.flush(), delay);
                } else if (!this.isOpen && error.status !== 409) {
                    this.updateAvailability(false);
                } else {
                    this.error = error.message || 'Kunne ikke gemme. Genindlæs siden, og prøv igen.';
                }
            } finally {
                this.busy = false;
                if (!this.destroyed && !this.error && Object.keys(this.pending).length) {
                    this.retryTimer = schedule(() => this.flush(), 0);
                }
            }
        },
        dispose() {
            this.destroyed = true;
            this.stopTimer();
        },
    };
}
