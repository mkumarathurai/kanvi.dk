@php
    $landing ??= true;
    $share = \App\Http\Controllers\ArticleController::HOME_SHARE;
    $seo = $landing ? [
        'title' => $share['title'].' · Kanvi',
        'description' => 'Find en dag, der passer gruppen. Opret en datoafstemning uden konto.',
        'canonical' => app(\App\Content\Articles::class)->url('/'),
        'image' => app(\App\Content\Articles::class)->url('/deling/'.$share['key']),
    ] : null;
@endphp
<x-layout :home="$landing" :seo="$seo">
    <livewire:create-poll :landing="$landing" />
</x-layout>
