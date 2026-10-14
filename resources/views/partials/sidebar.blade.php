@php($skin = Admin::skin())
<aside class="app-sidebar shadow {{ $skin['sidebar'] === 'dark' ? 'bg-body-secondary' : 'bg-body' }}" data-bs-theme="{{ $skin['sidebar'] }}">

    <div class="sidebar-brand">
        <a href="{{ admin_url('/') }}" class="brand-link">
            <span class="brand-text brand-text-mini">{!! config('admin.logo-mini', config('admin.name')) !!}</span>
            <span class="brand-text brand-text-lg">{!! config('admin.logo', config('admin.name')) !!}</span>
        </a>
    </div>

    <div class="sidebar-wrapper">

        <div class="user-panel d-flex align-items-center">
            <img src="{{ Admin::user()->avatar }}" class="rounded-circle shadow-sm" alt="User Image">
            <div class="info">
                <div class="name">{{ Admin::user()->name }}</div>
                <small><i class="fa-solid fa-circle text-success"></i> {{ trans('admin.online') }}</small>
            </div>
        </div>

        @if(config('admin.enable_menu_search'))
        <form class="sidebar-search-form dropdown" onsubmit="return false;">
            <div class="input-group input-group-sm">
                <input type="text" autocomplete="off" class="form-control autocomplete" placeholder="{{ trans('admin.search') }}...">
                <button type="submit" name="search" id="search-btn" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <ul class="dropdown-menu w-100" role="menu">
                @foreach(Admin::menuLinks() as $link)
                <li>
                    <a class="dropdown-item" href="{{ admin_url($link['uri']) }}"><i class="fa {{ $link['icon'] }} me-2"></i>{{ admin_trans($link['title']) }}</a>
                </li>
                @endforeach
            </ul>
        </form>
        @endif

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="{{ trans('admin.menu') }}" data-accordion="false">
                <li class="nav-header">{{ trans('admin.menu') }}</li>

                @each('admin::partials.menu', Admin::menu(), 'item')

            </ul>
        </nav>
    </div>
</aside>
