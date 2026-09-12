@extends('layouts.app')

@section('title', 'Статистика — Fordewind')

@section('assets')
    @vite(['resources/css/app.css', 'resources/js/statistics.js'])
@endsection

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div id="statistics-page"
             v-cloak
             data-statistics-api="{{ route('statistics.data', absolute: false) }}"
             data-statistics-models-api="{{ route('statistics.models', absolute: false) }}"></div>
        <noscript>
            <p class="rounded-lg bg-amber-100 p-4 text-amber-950">Для просмотра статистики необходимо включить JavaScript.
            </p>
        </noscript>
    </section>
@endsection