<div class="save-feedback" aria-live="polite" aria-atomic="true">
    <p id="save-status" :class="{ 'error': state === 'error' }" x-text="statusText">Dine valg gemmes automatisk.</p>
    <button type="button" class="text-button" x-cloak x-show="state === 'error' && retryable" @click="retry()" :disabled="busy">Prøv igen nu</button>
    <p class="hint" x-cloak x-show="notice" x-text="notice"></p>
</div>
