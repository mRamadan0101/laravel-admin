<!-- Main Header -->
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fa-solid fa-bars"></i>
                    <span class="visually-hidden">Toggle navigation</span>
                </a>
            </li>
            {!! Admin::getNavbar()->render('left') !!}
        </ul>

        <!-- Navbar Right Menu -->
        <ul class="navbar-nav ms-auto">

            {!! Admin::getNavbar()->render() !!}

            <li class="nav-item dropdown">
                <a class="nav-link" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('admin.theme') }}">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end admin-theme-switcher">
                    <li><button type="button" class="dropdown-item" data-admin-theme-value="light"><i class="fa-solid fa-sun me-2"></i>{{ __('admin.theme_light') }}</button></li>
                    <li><button type="button" class="dropdown-item" data-admin-theme-value="dark"><i class="fa-solid fa-moon me-2"></i>{{ __('admin.theme_dark') }}</button></li>
                    <li><button type="button" class="dropdown-item" data-admin-theme-value="auto"><i class="fa-solid fa-circle-half-stroke me-2"></i>{{ __('admin.theme_auto') }}</button></li>
                </ul>
            </li>

            <!-- User Account Menu -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="{{ Admin::user()->avatar }}" class="user-image rounded-circle shadow" alt="User Image">
                    <span class="d-none d-md-inline">{{ Admin::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <img src="{{ Admin::user()->avatar }}" class="rounded-circle shadow" alt="User Image">
                        <p>
                            {{ Admin::user()->name }}
                            <small>{{ __('admin.member_since') }} {{ Admin::user()->created_at }}</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="{{ admin_url('auth/setting') }}" class="btn btn-outline-secondary">{{ trans('admin.setting') }}</a>
                        <a href="{{ admin_url('auth/logout') }}" class="btn btn-outline-secondary float-end">{{ trans('admin.logout') }}</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
