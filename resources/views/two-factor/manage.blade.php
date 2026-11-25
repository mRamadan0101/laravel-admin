<div class="row g-3 two-factor">
    @if($recoveryCodes)
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header"><h3 class="card-title">{{ trans('admin.two_factor.recovery_codes') }}</h3></div>
            <div class="card-body">
                <p class="text-body-secondary">{{ trans('admin.two_factor.recovery_codes_help') }}</p>
                <div class="two-factor-codes">
                    @foreach($recoveryCodes as $code)
                        <code>{{ $code }}</code>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($user->hasTwoFactorEnabled())
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <span class="badge text-bg-success fs-6"><i class="fa-solid fa-shield-halved"></i> {{ trans('admin.two_factor.status_on') }}</span>
                <span class="text-body-secondary">
                    {{ trans('admin.two_factor.enabled_since', ['date' => $user->two_factor_confirmed_at->format('Y-m-d H:i')]) }}
                    &middot;
                    {{ trans('admin.two_factor.codes_left', ['count' => count((array) $user->two_factor_recovery_codes)]) }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <form method="post" action="{{ admin_url('auth/two-factor/recovery-codes') }}" class="card h-100">
            <div class="card-header"><h3 class="card-title">{{ trans('admin.two_factor.regenerate') }}</h3></div>
            <div class="card-body">
                <p class="text-body-secondary">{{ trans('admin.two_factor.regenerate_help') }}</p>
                <label class="form-label" for="two-factor-regenerate-password">{{ trans('admin.password') }}</label>
                <input type="password" name="password" id="two-factor-regenerate-password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" autocomplete="current-password" required>
            </div>
            <div class="card-footer">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-rotate"></i> {{ trans('admin.two_factor.regenerate') }}</button>
            </div>
        </form>
    </div>

    <div class="col-md-6">
        <form method="post" action="{{ admin_url('auth/two-factor') }}" class="card card-outline card-danger h-100">
            <div class="card-header"><h3 class="card-title">{{ trans('admin.two_factor.disable') }}</h3></div>
            <div class="card-body">
                <p class="text-body-secondary">{{ trans('admin.two_factor.disable_help') }}</p>
                <label class="form-label" for="two-factor-disable-password">{{ trans('admin.password') }}</label>
                <input type="password" name="password" id="two-factor-disable-password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" autocomplete="current-password" required>
                @foreach($errors->get('password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>
            <div class="card-footer">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-power-off"></i> {{ trans('admin.two_factor.disable') }}</button>
            </div>
        </form>
    </div>
    @else
    <div class="col-12">
        <form method="post" action="{{ admin_url('auth/two-factor') }}" class="card">
            <div class="card-header"><h3 class="card-title">{{ trans('admin.two_factor.setup') }}</h3></div>
            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-auto">
                        <div class="two-factor-qr" id="two-factor-qr" data-uri="{{ $uri }}" role="img" aria-label="{{ trans('admin.two_factor.scan') }}"></div>
                    </div>
                    <div class="col">
                        <ol class="two-factor-steps">
                            <li>{{ trans('admin.two_factor.scan') }}</li>
                            <li>
                                {{ trans('admin.two_factor.manual_key') }}
                                <div><code class="two-factor-secret user-select-all">{{ trim(chunk_split($secret, 4, ' ')) }}</code></div>
                            </li>
                            <li>
                                <label class="form-label" for="two-factor-code">{{ trans('admin.two_factor.enter_code') }}</label>
                                <input type="text" name="code" id="two-factor-code" class="form-control two-factor-code-input {{ $errors->has('code') ? 'is-invalid' : '' }}" inputmode="numeric" autocomplete="one-time-code" maxlength="7" placeholder="123456" required>
                                @foreach($errors->get('code') as $message)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @endforeach
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-shield-halved"></i> {{ trans('admin.two_factor.enable') }}</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var box = document.getElementById('two-factor-qr');

            function render() {
                var qr = qrcode(0, 'M');
                qr.addData(box.getAttribute('data-uri'));
                qr.make();
                box.innerHTML = qr.createSvgTag({cellSize: 4, margin: 2, scalable: true});
            }

            if (window.qrcode) {
                render();
            } else {
                var script = document.createElement('script');
                script.src = '{{ admin_asset('vendor/laravel-admin/qrcode-generator/qrcode.js') }}';
                script.onload = render;
                document.head.appendChild(script);
            }
        })();
    </script>
    @endif
</div>
