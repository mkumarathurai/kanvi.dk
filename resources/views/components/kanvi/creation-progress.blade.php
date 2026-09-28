@props(['step'])
<ol class="wizard-progress" aria-label="Oprettelse, trin {{ $step }} af 3">
    @foreach (['Titel', 'Datoer', 'Overblik'] as $label)
        <li @class(['current' => $step === $loop->iteration, 'complete' => $step > $loop->iteration]) @if ($step === $loop->iteration) aria-current="step" @endif>
            <span>{{ $step > $loop->iteration ? '✓' : $loop->iteration }}</span><span>{{ $label }}</span>
        </li>
    @endforeach
</ol>
