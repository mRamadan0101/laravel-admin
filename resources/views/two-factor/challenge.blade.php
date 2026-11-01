<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('app.locale')) }}" dir="{{ Admin::isRtl() ? 'rtl' : 'ltr' }}" data-bs-theme="{{ Admin::theme() === 'dark' ? 'dark' : 'light' }}" data-admin-theme="{{ Admin::theme() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <title>{{config('admin.title')}} | {{ trans('admin.two_factor.title') }}</title>

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

  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/fontawesome/css/all.min.css") }}">
  <link rel="stylesheet" href="{{ admin_asset(Admin::isRtl() ? "vendor/laravel-admin/AdminLTE/dist/css/adminlte.rtl.min.css" : "vendor/laravel-admin/AdminLTE/dist/css/adminlte.min.css") }}">
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/google-fonts/fonts.css") }}">
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/laravel-admin/laravel-admin.css") }}">
</head>
<body class="login-page bg-body-secondary skin-{{ Admin::skin()['color'] }}" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>
<div class="login-box">
  <div class="login-logo">
    <a href="{{ admin_url('/') }}"><b>{{config('admin.name')}}</b></a>
  </div>

  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">
        <i class="fa-solid fa-shield-halved text-primary fs-3 d-block mb-2"></i>
        <span class="two-factor-hint" data-mode="code">{{ trans('admin.two_factor.challenge_code') }}</span>
        <span class="two-factor-hint d-none" data-mode="recovery">{{ trans('admin.two_factor.challenge_recovery') }}</span>
      </p>

      <form action="{{ admin_url('auth/two-factor-challenge') }}" method="post">
        <div class="mb-3 two-factor-field" data-mode="code">
          <div class="input-group">
            <input type="text" class="form-control two-factor-code-input {{ $errors->has('code') ? 'is-invalid' : '' }}" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" placeholder="123456" aria-label="{{ trans('admin.two_factor.code') }}" autofocus>
            <div class="input-group-text"><span class="fa-solid fa-mobile-screen"></span></div>
          </div>
        </div>

        <div class="mb-3 two-factor-field d-none" data-mode="recovery">
          <div class="input-group">
            <input type="text" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" name="recovery_code" autocomplete="off" placeholder="XXXXX-XXXXX" aria-label="{{ trans('admin.two_factor.recovery_code') }}" disabled>
            <div class="input-group-text"><span class="fa-solid fa-life-ring"></span></div>
          </div>
        </div>

        @foreach($errors->get('code') as $message)
          <div class="invalid-feedback d-block mb-3"><i class="fa-regular fa-circle-xmark"></i> {{ $message }}</div>
        @endforeach

        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary">{{ trans('admin.two_factor.verify') }}</button>
        </div>

        <div class="d-flex justify-content-between small">
          <a href="#" class="two-factor-toggle" data-mode="code">{{ trans('admin.two_factor.use_recovery') }}</a>
          <a href="#" class="two-factor-toggle d-none" data-mode="recovery">{{ trans('admin.two_factor.use_code') }}</a>
          <a href="{{ admin_url('auth/login') }}">{{ trans('admin.back') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.two-factor-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (event) {
      event.preventDefault();
      var mode = toggle.getAttribute('data-mode') === 'code' ? 'recovery' : 'code';
      document.querySelectorAll('[data-mode]').forEach(function (el) {
        var active = el.getAttribute('data-mode') === mode;
        el.classList.toggle('d-none', !active);
        el.querySelectorAll('input').forEach(function (input) {
          input.disabled = !active;
          if (active) { input.focus(); }
        });
      });
    });
  });
</script>
</body>
</html>
