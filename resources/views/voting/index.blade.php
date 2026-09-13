@extends('layouts.app')

@section('title', 'Голосование')

@section('assets')
    @vite(['resources/css/app.css', 'resources/js/voting.js'])
@endsection

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h1 class="text-3xl font-bold tracking-tight">Голосование за фотографии</h1>
        <div id="voting-page"
             class="mt-6"
             data-state="initial">
            <div class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">
                <label class="block text-sm font-semibold text-slate-800"
                       for="voting-model">Модель автомобиля</label>
                <select id="voting-model"
                        name="voting-model"
                        class="mt-2 w-full rounded-lg border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-600 focus:ring-sky-600"
                        disabled>
                    <option value="">Загрузка моделей…</option>
                </select>
                <p id="voting-status"
                   class="mt-3 text-sm text-slate-600"
                   aria-live="polite">Загружаем доступные модели.</p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2"
                 aria-live="polite">
                @foreach (['left' => 'Левая нравится больше', 'right' => 'Правая нравится больше'] as $side => $label)
                    <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
                        <div class="voting-photo relative aspect-[4/3] bg-slate-200">
                            <img id="voting-{{ $side }}-image"
                                 class="h-full w-full object-contain"
                                 alt=""
                                 hidden>
                        </div>
                        <div class="p-4">
                            <button class="w-full cursor-pointer rounded-lg bg-sky-700 px-4 py-3 font-semibold text-white transition hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                    type="button"
                                    data-side="{{ $side }}"
                                    disabled>
                                {{ $label }}
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
        <noscript>
            <p class="mt-4 rounded-lg bg-amber-100 p-4 text-amber-950">Для голосования необходимо включить JavaScript.</p>
        </noscript>
    </section>
@endsection