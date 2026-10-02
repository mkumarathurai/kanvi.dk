@php
    $landing ??= true;
    // The sharing image is a real screenshot of this hero at 1440 px, scaled to
    // 1200x630. Retake it when the hero changes.
    $seo = $landing ? [
        'title' => 'Find en dag, der passer alle – Kanvi?',
        'description' => 'Find en dag, der passer gruppen. Opret en datoafstemning uden konto.',
        'canonical' => app(\App\Content\Articles::class)->url('/'),
        'image' => app(\App\Content\Articles::class)->url('/images/share/kanvi-forside.png'),
    ] : null;
@endphp
<x-layout :home="$landing" :seo="$seo">
    <livewire:create-poll :landing="$landing" />
</x-layout>
