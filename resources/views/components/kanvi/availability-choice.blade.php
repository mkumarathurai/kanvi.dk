@props(['option', 'value', 'label'])
<button type="button" class="vote-choice {{ $value }}" :class="{ 'chosen': answers['{{ $option }}'] === '{{ $value }}' }"
    :aria-pressed="answers['{{ $option }}'] === '{{ $value }}'" @click="choose('{{ $option }}', '{{ $value }}')">
    <span class="choice-symbol" aria-hidden="true">{{ ['can' => '✓', 'maybe' => '−', 'cannot' => '×'][$value] }}</span>
    <span>{{ $label }}</span>
    <span class="choice-selected" aria-hidden="true" x-show="answers['{{ $option }}'] === '{{ $value }}'">Valgt</span>
</button>
