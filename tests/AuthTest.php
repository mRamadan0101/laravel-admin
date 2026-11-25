<?php

class AuthTest extends TestCase
{
    public function testLoginPage()
    {
        $this->visit('admin/auth/login')
            ->see('login');
    }

    public function testVisitWithoutLogin()
    {
        $this->visit('admin')
            ->dontSeeIsAuthenticated('admin')
            ->seePageIs('admin/auth/login');
    }

    public function testLogin()
    {
        $credentials = ['username' => 'admin', 'password' => 'admin'];

        $this->visit('admin/auth/login')
            ->see('login')
            ->submitForm('Login', $credentials)
            ->see('dashboard')
            ->seeCredentials($credentials, 'admin')
            ->seeIsAuthenticated('admin')
            ->seePageIs('admin')
            ->see('Dashboard')
            ->see('Description...')

            ->see('Environment')
            ->see('PHP version')
            ->see('Laravel version')

            ->see('Available extensions')
            ->seeLink('laravel-admin-ext/helpers', 'https://github.com/laravel-admin-extensions/helpers')
            ->seeLink('laravel-admin-ext/backup', 'https://github.com/laravel-admin-extensions/backup')
            ->seeLink('laravel-admin-ext/media-manager', 'https://github.com/laravel-admin-extensions/media-manager')

            ->see('Dependencies');

        $this
            ->seeInElement('.sidebar-menu .nav-link p', 'Admin')
            ->seeInElement('.sidebar-menu .nav-link p', 'Users')
            ->seeInElement('.sidebar-menu .nav-link p', 'Roles')
            ->seeInElement('.sidebar-menu .nav-link p', 'Permission')
            ->seeInElement('.sidebar-menu .nav-link p', 'Operation log')
            ->seeInElement('.sidebar-menu .nav-link p', 'Menu');
    }

    public function testLoginIsThrottledAfterTooManyFailures()
    {
        config(['admin.auth.throttle.max_attempts' => 3]);

        foreach (range(1, 3) as $attempt) {
            $this->visit('admin/auth/login')
                ->submitForm('Login', ['username' => 'admin', 'password' => 'wrong'])
                ->seePageIs('admin/auth/login')
                ->dontSee('Too many login attempts');
        }

        $this->visit('admin/auth/login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin'])
            ->seePageIs('admin/auth/login')
            ->see('Too many login attempts')
            ->dontSeeIsAuthenticated('admin');
    }

    public function testSuccessfulLoginClearsFailedAttempts()
    {
        config(['admin.auth.throttle.max_attempts' => 3]);

        foreach (range(1, 2) as $attempt) {
            $this->visit('admin/auth/login')
                ->submitForm('Login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $this->visit('admin/auth/login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin'])
            ->seeIsAuthenticated('admin')
            ->seePageIs('admin');
    }

    public function testLogout()
    {
        $this->visit('admin/auth/logout')
            ->seePageIs('admin/auth/login')
            ->dontSeeIsAuthenticated('admin');
    }
}
