<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        @if($user->hasTwoFactorEnabled())
            <span class="badge text-bg-success"><i class="fa-solid fa-shield-halved"></i> {{ trans('admin.two_factor.status_on') }}</span>
        @else
            <span class="badge text-bg-secondary"><i class="fa-solid fa-shield"></i> {{ trans('admin.two_factor.status_off') }}</span>
        @endif
        <div class="text-body-secondary small mt-2">{{ trans('admin.two_factor.description') }}</div>
    </div>
    <a href="{{ admin_url('auth/two-factor') }}" class="btn btn-sm btn-outline-primary">
        <i class="fa-solid fa-key"></i> {{ trans('admin.two_factor.manage') }}
    </a>
</div>
