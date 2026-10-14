<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('app.locale')) }}" dir="{{ Admin::isRtl() ? 'rtl' : 'ltr' }}" data-bs-theme="{{ Admin::theme() === 'dark' ? 'dark' : 'light' }}" data-admin-theme="{{ Admin::theme() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>

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
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/fontawesome/css/v4-shims.min.css") }}">
  <link rel="stylesheet" href="{{ admin_asset(Admin::isRtl() ? "vendor/laravel-admin/AdminLTE/dist/css/adminlte.rtl.min.css" : "vendor/laravel-admin/AdminLTE/dist/css/adminlte.min.css") }}">
</head>
<body class="login-page bg-body-secondary" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>
<div class="login-box">
  <div class="login-logo">
    <a href="{{ admin_url('/') }}"><b>{{config('admin.name')}}</b></a>
  </div>

  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">{{ trans('admin.login') }}</p>

      <form action="{{ admin_url('auth/login') }}" method="post">

        <div class="mb-3 {!! !$errors->has('username') ?: 'has-error' !!}">
          <div class="input-group">
            <input type="text" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" placeholder="{{ trans('admin.username') }}" name="username" value="{{ old('username') }}" autofocus>
            <div class="input-group-text"><span class="fa-solid fa-user"></span></div>
          </div>
          @if($errors->has('username'))
            @foreach($errors->get('username') as $message)
              <label class="invalid-feedback d-block" for="inputError"><i class="fa-regular fa-circle-xmark"></i> {{$message}}</label>
            @endforeach
          @endif
        </div>

        <div class="mb-3 {!! !$errors->has('password') ?: 'has-error' !!}">
          <div class="input-group">
            <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="{{ trans('admin.password') }}" name="password">
            <div class="input-group-text"><span class="fa-solid fa-lock"></span></div>
          </div>
          @if($errors->has('password'))
            @foreach($errors->get('password') as $message)
              <label class="invalid-feedback d-block" for="inputError"><i class="fa-regular fa-circle-xmark"></i> {{$message}}</label>
            @endforeach
          @endif
        </div>

        <div class="row align-items-center">
          <div class="col-8">
            @if(config('admin.auth.remember'))
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
              <label class="form-check-label" for="remember">{{ trans('admin.remember_me') }}</label>
            </div>
            @endif
          </div>
          <div class="col-4">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">{{ trans('admin.login') }}</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ admin_asset("vendor/laravel-admin/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
</body>
</html>
