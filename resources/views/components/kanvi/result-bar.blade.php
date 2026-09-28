<div class="result-bar" aria-hidden="true">
    <template x-for="value in ['can', 'maybe', 'cannot', 'unanswered']" :key="value">
        <span :class="value" x-show="option[value] > 0" :style="{ flexGrow: option[value] }" x-text="option[value]"></span>
    </template>
</div>
