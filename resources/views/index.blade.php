<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('app.locale')) }}" dir="{{ Admin::isRtl() ? 'rtl' : 'ltr' }}" data-bs-theme="{{ Admin::theme() === 'dark' ? 'dark' : 'light' }}" data-admin-theme="{{ Admin::theme() }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ Admin::title() }} @if($header) | {{ $header }}@endif</title>

    @if(!is_null($favicon = Admin::favicon()))
    <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('laravel-admin.theme'); } catch (e) {}
            var theme = stored || document.documentElement.getAttribute('data-admin-theme');
            if (theme === 'auto') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    {!! Admin::css() !!}

    <script src="{{ Admin::jQuery() }}"></script>
    {!! Admin::headerJs() !!}
</head>

<body class="{{ Admin::bodyClass() }}">

@if($alert = config('admin.top_alert'))
    <div class="admin-top-alert">
        {!! $alert !!}
    </div>
@endif

<div class="app-wrapper">

    @include('admin::partials.header')

    @include('admin::partials.sidebar')

    <main class="app-main" id="pjax-container">
        {!! Admin::style() !!}
        <div id="app">
        @yield('content')
        </div>
        {!! Admin::script() !!}
        {!! Admin::html() !!}
    </main>

    @include('admin::partials.footer')

</div>

<button id="totop" class="btn btn-primary" title="Go to top" style="display: none;"><i class="fa-solid fa-chevron-up"></i></button>

<script>
    function LA() {}
    LA.token = "{{ csrf_token() }}";
    LA.user = @json($_user_);
</script>

<!-- REQUIRED JS SCRIPTS -->
{!! Admin::js() !!}

</body>
</html>
