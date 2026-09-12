<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <meta name="csrf-token"
          content="{{ csrf_token() }}">
    <title>@yield('title', 'Fordewind')</title>
    @yield('assets')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center gap-6 px-4 py-4 sm:px-6"
             aria-label="Основная навигация">
            <a class="text-lg font-semibold text-slate-900"
               href="{{ route('voting.page') }}">Fordewind</a>
            <a class="font-medium text-slate-700 hover:text-slate-950"
               href="{{ route('voting.page') }}">Голосование</a>
            <a class="font-medium text-slate-700 hover:text-slate-950"
               href="{{ route('statistics.page') }}">Статистика</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>